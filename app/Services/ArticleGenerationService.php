<?php

namespace App\Services;

use App\Helper;
use App\Models\Category;
use App\Models\GeneratedPiece;
use App\Models\GeneratedPost;
use App\Models\Keyword;
use App\Models\Piece;
use App\Models\Serp;
use App\Models\Spell;
use Illuminate\Support\Collection;

class ArticleGenerationService
{

    const QTY_OF_REWRITES = 8;

    const BLACKLISTED_URLS = [
        'wikipedia'
    ];

    /**
     * @var UniquenessTestingService
     */
    private $uniquenessService;
    /**
     * @var HeadingGenerationService
     */
    private $headingGenerationService;
    private $badWordsInHeadings = [
        'article',
        'related',
        'link',
        'resource',
        'in touch',
        'contact',
        'resource',
        'log in',
        'navigation',
        'posts',
        'email',
        'comment',
        'confirm',
        'hello',
        'downloads',
        'sponsored',
        'tag',
        'search',
        'event',
        'recently asked',
    ];

    public function __construct(UniquenessTestingService $uniquenessService, HeadingGenerationService $headingGenerationService)
    {
        $this->uniquenessService = $uniquenessService;
        $this->headingGenerationService = $headingGenerationService;
    }

    public $stopWords = [
        'click',
        'form below'
    ];

    private $badCharacters = ['{', '}', '/', '\\'];

    public function generateArticle(Keyword $keyword, $i, $loggerFn)
    {
        $start = now();
        $loggerFn('number %s', $i);
        $this->cleanPiecesForKeyword($keyword);
        $loggerFn('choosing');
        /** @var Serp $serp */
        $serp = $this->chooseBestSource($keyword);
        if ($serp) {
            $loggerFn('keyword: %s', $keyword->keyword);
            $loggerFn('serp title: %s', $serp->title);
            $loggerFn('serp  url: %s', $serp->url);
            $loggerFn('scores');
            foreach ($serp->scores as $scoreName => $score) {
                $loggerFn('%s: %s', $scoreName, $score);
            }
            $loggerFn('----------');
            $loggerFn('----------');
        }

        if ($serp) {
            $loggerFn('rewritePieces');
            $this->rewritePieces($serp);
            $serp->refresh();
            $loggerFn('cleanGeneratedPiecesForSerp');
            $this->cleanGeneratedPiecesForSerp($serp);
            $serp->refresh();
            $this->saveEmbeddings($serp);
            $loggerFn('chooseGeneratedPieces');
            $serp->refresh();
            $this->chooseGeneratedPieces($serp);

            $keyword->refresh();
            $serp->refresh();

            $loggerFn('generateHeadings');
            $this->generateHeadings($serp);
            $keyword->refresh();

            $loggerFn('finalizePost');
            FinalizingService::finalizePost($keyword);

            $category = Category::query()->find(15);//uk scholarships
            $generatedPost = PostCompositionService::saveGeneratedPost($keyword, $category);
            $initialLength = $generatedPost->words();
            $desiredLength = max(round($initialLength * 1.3), 600);

            $loggerFn('lengthenPost');
            while($generatedPost->words() < $desiredLength) {
                $loggerFn('lengthening the post, current length %s words', $generatedPost->words());
                FinalizingService::lengthenPost($keyword);
                $generatedPost->refresh();
            }
        }

        $timeTaken = $start->diffInMilliseconds(now()) / 1000;
        $loggerFn('took %s seconds', $timeTaken);
    }

    public function chooseGeneratedPieces(Serp $serp)
    {
        $chosenGeneratedPiecesIds = [];
        foreach ($serp->pieces as $piece) {
            $distances = [];
            foreach ($piece->generatedPieces as $generatedPiece) {
                $distance = $this->getDistance($piece, $generatedPiece);
                $generatedPiece->distance_from_original = $distance;
                $generatedPiece->save();
                $distances[$generatedPiece->id] = $distance;
            }

            if (count($distances)) {
                $chosenGeneratedPiecesIds[] = array_search(min($distances), $distances);
            }
        }

        GeneratedPiece::query()
            ->whereIn('id', $chosenGeneratedPiecesIds)
            ->update(['chosen' => true]);
    }

    private function cleanGeneratedPiecesForSerp(Serp $serp)
    {
        $good = new Collection();

        $serp->load(['generatedPieces', 'generatedPieces.piece']);

        foreach ($serp->generatedPieces as $generatedPiece) {
            $generatedPiece = $this->removeSentencesWithStopWords($generatedPiece);

            $words = count(explode(' ', $generatedPiece->content));
            $wordsInSource = count(explode(' ', $generatedPiece->piece->content));
            if ($words / $wordsInSource < 0.5) {
                continue;
            }

            if (Helper::strpos_arr($generatedPiece->content, $this->badCharacters)) {
                continue;
            }

            $uniqueFromOriginal = !$this->uniquenessService
                ->hasDuplicates($generatedPiece->content, [$generatedPiece->piece->content], 15);

            if($uniqueFromOriginal) {
                $generatedPiece->save();
                $good->add($generatedPiece);
            }
        }

        GeneratedPiece::query()
            ->whereNotIn('id', $good->pluck('id')->toArray())
            ->where('serp_id', '=', $serp->id)
            ->delete();
    }

    private function rewritePieces(Serp $serp)
    {
        /** @var Spell $spell */
        $spell = Spell::find(5);

        foreach ($serp->pieces as $piece) {
            if ($piece->generatedPieces->count()) {
                continue;
            }

            $rewrittenTexts = TextGenerationService::generate($piece->content, $spell, self::QTY_OF_REWRITES);
            foreach ($rewrittenTexts as $rewrittenText) {
                $generatedPiece = new GeneratedPiece();
                $generatedPiece->original_piece_id = $piece->id;
                $generatedPiece->heading = $piece->heading;
                $generatedPiece->keyword_id = $piece->keyword_id;
                $generatedPiece->content = $rewrittenText;
                $generatedPiece->serp_id = $serp->id;
                $generatedPiece->save();
            }
        }
    }

    private function chooseBestSource(Keyword $keyword)
    {
        Serp::query()
            ->where('keyword_id', '=', $keyword->id)
            ->update(['chosen' => 0]);

        Piece::query()
            ->where('keyword_id', '=', $keyword->id)
            ->update(['chosen' => 0]);

        $serp = $this->getBestSerpByKeyword($keyword);
        if (!$serp) {
            return null;
        }

        $serp->chosen = true;
        $serp->save();

        Piece::query()
            ->where('serp_id', '=', $serp->id)
            ->update(['chosen' => 1]);

        return $serp;
    }

    private function getBestSerpByKeyword(Keyword $keyword)
    {
        $keyword->refresh();
        $serps = new Collection;

        /** @var Serp $serp */
        foreach ($keyword->serps as $serp) {
            if (!$serp->pieces->count()) {
                continue;
            }

            foreach (self::BLACKLISTED_URLS as $blackListedUrl) {
                if (str_contains($serp->url, $blackListedUrl)) {
                    continue(2);
                }
            }

            $serps->add($serp);
        }

        /** @var SerpScoringService $scoringService */
        $scoringService = resolve(SerpScoringService::class);
        $serpId = $scoringService->rank($serps);
        if (!$serpId) {
            return null;
        }

        return $keyword->serps->keyBy('id')->get($serpId);
    }

    private function cleanPiecesForKeyword(Keyword $keyword)
    {
        $bad = [];
        foreach ($keyword->pieces as $piece) {
            if (Helper::strpos_arr($piece->content, $this->badCharacters)) {
                $bad[] = $piece->id;
                continue;
            }

            if (Helper::strpos_arr($piece->content, $this->stopWords)) {
                $bad[] = $piece->id;
                continue;
            }

            if (Helper::strpos_arr(mb_strtolower($piece->heading), $this->badWordsInHeadings)) {
                $bad[] = $piece->id;
                continue;
            }

            if (strlen($piece->heading) < 5) {
                $bad[] = $piece->id;
                continue;
            }

            if($piece->words() < 20) {
                $bad[] = $piece->id;
            }
        }

        Piece::query()
            ->whereIn('id', $bad)
            ->delete();

        $keyword->refresh();
        $serpIdsForDeletion = [];

        foreach ($keyword->serps as $serp) {
            $uniquePieces = new Collection();
            foreach ($serp->pieces as $piece) {
                $existingTexts = $uniquePieces->except([$piece->id])->pluck('content')->toArray();
                $unique = !$this->uniquenessService->hasDuplicates($piece->content, $existingTexts, 15, []);
                if($unique) {
                    $uniquePieces->add($piece);
                } else {
                    $serpIdsForDeletion[] = $piece->id;
                }
            }
        }

        Piece::query()
            ->whereIn('id', $serpIdsForDeletion)
            ->delete();
    }

    /**
     * @param Piece|GeneratedPiece $piece
     * @param Piece|GeneratedPiece $otherPiece
     * @return float
     */
    public static function getDistance($piece, $otherPiece): float
    {
        $sum = 0;
        $left = $piece->embedding;
        $right = $otherPiece->embedding;
        foreach ($left as $key => $number1) {
            $number2 = $right[$key];
            $sum += ($number1 - $number2) ** 2;
        }

        return sqrt($sum);
    }

    public function removeSentencesWithStopWords(GeneratedPiece $generatedPiece)
    {
        $sentences = collect(preg_split('/(?<!Mr.|Mrs.|Ms.|Dr.|St.)(?<=[.?!;])\s+/', $generatedPiece->content, -1, PREG_SPLIT_NO_EMPTY));
        $sentences = $sentences->filter(function ($sentence) {
            return Helper::strpos_arr($sentence, $this->stopWords) === false;
        })->toArray();

        $generatedPiece->content = implode(' ', $sentences);
        return $generatedPiece;
    }

    private function saveEmbeddings(Serp $serp)
    {
        $payload = [];
        foreach ($serp->pieces as $piece) {
            if (!$piece->embedding) {
                $payload[$piece->id] = $piece->content;
            }
        }

        if (count($payload)) {
            $result = TextGenerationService::embeddings($payload);

            foreach ($result as $id => $vector) {
                Piece::query()
                    ->where('id', '=', $id)
                    ->update(['embedding' => json_encode($vector)]);
            }
        }

        $payload = [];
        foreach ($serp->generatedPieces as $generatedPiece) {
            if (!$generatedPiece->embedding) {
                $payload[$generatedPiece->id] = $generatedPiece->content;
            }
        }

        if (count($payload)) {
            $result = TextGenerationService::embeddings($payload);

            foreach ($result as $id => $vector) {
                GeneratedPiece::query()
                    ->where('id', '=', $id)
                    ->update(['embedding' => json_encode($vector)]);
            }
        }
    }

    public function generateHeadings(Serp $serp)
    {
        $this->headingGenerationService->generateHeadings($serp);
        $this->headingGenerationService->chooseHeadings($serp);
    }
}
