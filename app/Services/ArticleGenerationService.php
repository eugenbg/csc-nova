<?php

namespace App\Services;

use App\Helper;
use App\Models\Category;
use App\Models\GeneratedPiece;
use App\Models\Keyword;
use App\Models\Piece;
use App\Models\ReferenceTextPiece;
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
        'form below',
        '__',
        'apply here'
    ];

    private $badCharacters = ['{', '}', '/', '\\', '~'];

    public function generateArticle(Keyword $keyword, $i, $loggerFn)
    {
        GeneratedPiece::query()
            ->where('keyword_id', '=', $keyword->id)
            ->delete();

        $start = now();
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
        } else {
            $loggerFn('COULD NOT FIND A GOOD SOURCE FOR REWRITE');
        }

        if ($serp) {
            $loggerFn('rewritePieces');
            $start = now();
            $this->rewritePieces($serp);
            $serp->refresh();
            $loggerFn('rewritePieces took %s sec', now()->diffInMilliseconds($start) / 1000);

            $loggerFn('cleanGeneratedPiecesForSerp');
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

            $category = Category::query()->find(15);
            $generatedPost = PostCompositionService::saveGeneratedPost($keyword, $category);

            $keyword->refresh();
            GooglePlacesService::saveKeywordData($keyword);

            $initialLength = $generatedPost->words();
            $desiredLength = max(round($initialLength * 1.3), 600);

            $loggerFn('lengthenPost');
            $i = 0;
            while ($generatedPost->words() < $desiredLength && $i >= 3) {
                $i++;
                $loggerFn('lengthening the post, current length %s words', $generatedPost->words());
                FinalizingService::lengthenPost($keyword);
                $generatedPost->refresh();
            }

            $generatedPost->source_url = $serp->url;
            $generatedPost->save();
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

            $this->improveGeneratedPiece($generatedPiece);

            if ($this->isGeneratedPieceGood($generatedPiece)) {
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

        /**
         * make 3 shots for each piece
         */
        foreach ($serp->pieces as $piece) {
            $qty = $i = 0;
            while($qty < 2 && $i <= 3) {
                $i++;
                $qty = $this->generatePiecesForSourcePiece($piece, $spell);
                $a = 0;
            }

            $b = 0;
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

    public function getBestSerpByKeyword(Keyword $keyword)
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
        $serpIds = $scoringService->rank($serps);
        if (!count($serpIds)) {
            return null;
        }

        $winnerByEmbedding = $this->getBestSerpByEmbedding($serpIds);

        return $keyword->serps->keyBy('id')->get($winnerByEmbedding);
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

            if ($piece->words() < 20) {
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
                if ($unique) {
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

    private function getBestSerpByEmbedding(?array $serpIds)
    {
        $refPieces = ReferenceTextPiece::query()
            ->where('reference_text_id', '=', 1)
            ->get();

        /** @var ReferenceTextPiece $refPiece */
        foreach ($refPieces as $refPiece) {
            if (!$refPiece->embedding) {
                $refPiece->embedding = TextGenerationService::embeddings([$refPiece->text])[0];
                $refPiece->save();
            }
        }

        $serps = Serp::query()->whereIn('id', $serpIds)->get();
        /** @var Serp $serp */
        foreach ($serps as $serp) {
            $this->saveEmbeddings($serp);
            $serp->refresh();
        }

        $results = [];
        foreach ($serps as $serp) {
            $results[$serp->id] = 0;

            foreach ($serp->pieces as $piece) {
                $distances = [];
                foreach ($refPieces as $refPiece) {
                    $distances[] = EmbeddingDistanceService::getDistance($piece->embedding, $refPiece->embedding);
                }

                $results[$serp->id] += min($distances);
            }

            $results[$serp->id] = $results[$serp->id] / $serp->pieces->count();
        }

        foreach ($results as $serpId => $distance) {
            Serp::query()
                ->where('id', '=', $serpId)
                ->update(['distance_to_ref' => $distance]);
        }

        $bestDistance = min($results);

        if($bestDistance > 0.68) {
            return null;
        }

        return array_search($bestDistance, $results);
    }

    private function isGeneratedPieceGood($generatedPiece)
    {
        $words = count(explode(' ', $generatedPiece->content));
        $wordsInSource = count(explode(' ', $generatedPiece->piece->content));
        if ($words / $wordsInSource < 0.5) {
            return false;
        }

        if (Helper::strpos_arr($generatedPiece->content, $this->badCharacters)) {
            return false;
        }

        $uniqueFromOriginal = !$this->uniquenessService
            ->hasDuplicates($generatedPiece->content, [$generatedPiece->piece->content], 15);

        return $uniqueFromOriginal;
    }

    private function improveGeneratedPiece($generatedPiece)
    {
        $generatedPiece = $this->removeSentencesWithStopWords($generatedPiece);

        if(Helper::isTextCaps($generatedPiece->content)) {
            $generatedPiece->content = Helper::deCapitalizeText($generatedPiece->content);
        }

        $generatedPiece->content = str_replace('"', '', $generatedPiece->content);
        $generatedPiece->save();
    }

    private function generatePiecesForSourcePiece(Piece $piece, Spell $spell)
    {
        $qty = $piece->generatedPieces->count();
        $serp = $piece->serp;
        $rewrittenTexts = TextGenerationService::generate($piece->content, $spell, self::QTY_OF_REWRITES);
        foreach ($rewrittenTexts as $rewrittenText) {
            $generatedPiece = new GeneratedPiece();
            $generatedPiece->piece_id = $piece->id;
            $generatedPiece->heading = $piece->heading;
            $generatedPiece->keyword_id = $piece->keyword_id;
            $generatedPiece->content = trim($rewrittenText);
            $generatedPiece->serp_id = $serp->id;
            $generatedPiece->save();

            $generatedPiece->refresh();

            $this->improveGeneratedPiece($generatedPiece);

            if ($this->isGeneratedPieceGood($generatedPiece)) {
                $qty++;
            } else {
                $generatedPiece->delete();
            }
        }

        return $qty;
    }
}
