<?php

namespace App\Services;

use App\Models\GeneratedPiece;
use App\Models\Keyword;
use App\Models\Piece;
use App\Models\Serp;
use App\Models\Spell;
use Illuminate\Support\Collection;

class ArticleGenerationService {

    /**
     * @var UniquenessTestingService
     */
    private $uniquenessService;

    public function __construct(UniquenessTestingService $uniquenessService)
    {
        $this->uniquenessService = $uniquenessService;
    }

    public $stopWords = ['click'];

    private $badCharacters = ['{', '}', '/', '\\'];

    public function generateArticle(Keyword $keyword)
    {
        $this->cleanPiecesForKeyword($keyword);
        $this->choosePiecesByBestArticle($keyword);
        $this->rewritePieces($keyword);
        $this->cleanGeneratedPiecesForKeyword($keyword);
        $this->chooseGeneratedPieces($keyword);
    }

    public function chooseGeneratedPieces(Keyword $keyword)
    {
        $chosenGeneratedPiecesIds = [];
        foreach ($keyword->pieces as $piece) {
            $distances = [];
            foreach ($piece->generatedPieces as $generatedPiece) {
                $distance = $this->getDistance($piece, $generatedPiece);
                $generatedPiece->distance_from_original = $distance;
                $generatedPiece->save();
                $distances[$generatedPiece->id] = $distance;
            }

            if(count($distances)) {
                $chosenGeneratedPiecesIds[] = array_search(min($distances), $distances);
            }
        }

        GeneratedPiece::query()
            ->whereIn('id', $chosenGeneratedPiecesIds)
            ->update(['chosen' => true]);
    }

    private function cleanGeneratedPiecesForKeyword(Keyword $keyword)
    {
        $good = new Collection();

        foreach ($keyword->generatedPieces as $generatedPiece) {
            $words = explode(' ', $generatedPiece->content);
            if(count($words) < 50) {
                continue;
            }

            if($this->strpos_arr($generatedPiece->content, $this->badCharacters)) {
                continue;
            }

            $this->removeSentencesWithStopWords($generatedPiece);
            $generatedPiece->save();

            $good->add($generatedPiece);
        }

        $good = $good->keyBy('id');
        $goodUnique = new Collection();

        /** @var GeneratedPiece $generatedPiece */
        foreach ($good as $generatedPiece) {
            $existingTexts = $good->except([$generatedPiece->id])->pluck('content')->toArray();
            $existingTexts[] = $generatedPiece->piece->content;
            $unique = !$this->uniquenessService->hasDuplicates($generatedPiece->content, $existingTexts, 15, []);
            if($unique) {
                $goodUnique->add($generatedPiece);
            }
        }

        GeneratedPiece::query()
            ->whereNotIn('id', $goodUnique->pluck('id')->toArray())
            ->where('keyword_id', '=', $keyword->id)
            ->delete();

        $payload = [];
        foreach ($goodUnique as $generatedPiece) {
            if(!$generatedPiece->embedding) {
                $payload[$generatedPiece->id] = $generatedPiece->content;
            }
        }

        if(count($payload)) {
            $result = TextGenerationService::embeddings($payload);

            foreach ($result as $id => $vector) {
                GeneratedPiece::query()
                    ->where('id', '=', $id)
                    ->update(['embedding' => json_encode($vector)]);
            }
        }
    }

    private function rewritePieces(Keyword $keyword)
    {
        /** @var Spell $spell */
        $spell = Spell::find(5);

        foreach ($keyword->pieces as $piece) {
            if($piece->chosen) {
                $rewrittenTexts = TextGenerationService::generate($piece->content, $spell, 7);
                foreach ($rewrittenTexts as $rewrittenText) {
                    $generatedPiece = new GeneratedPiece();
                    $generatedPiece->original_piece_id = $piece->id;
                    $generatedPiece->heading = $piece->heading;
                    $generatedPiece->keyword_id = $piece->keyword_id;
                    $generatedPiece->content = $rewrittenText;
                    $generatedPiece->save();
                }
            }
        }
    }

    private function choosePiecesByBestArticle(Keyword $keyword)
    {
        $serp = $this->getBestSerpByKeyword($keyword);
        Piece::query()
            ->where('serp_id', '=', $serp->id)
            ->update(['chosen' => 1]);
    }

    private function getBestSerpByKeyword(Keyword $keyword)
    {
        $wordsBySerp = [];
        $daBySerp = [];
        $linksBySerp = [];

        /** @var Serp $serp */
        foreach ($keyword->serps as $serp) {
            if(!$serp->pieces->count()) {
                continue;
            }

            $daBySerp[$serp->id] = $serp->da;
            $linksBySerp[$serp->id] = $serp->links;
            $wordsBySerp[$serp->id] = 0;
            foreach ($serp->pieces as $piece) {
                $wordsBySerp[$serp->id] += $piece->words();
            }
        }

        $serpId = array_search(max($wordsBySerp), $wordsBySerp);

        return $keyword->serps->keyBy('id')->get($serpId);
    }

    private function cleanPiecesForKeyword(Keyword $keyword)
    {
        $good = new Collection();
        foreach ($keyword->pieces as $piece) {
            $words = explode(' ', $piece->content);
            if(count($words) < 50) {
                continue;
            }

            if($this->strpos_arr($piece->content, $this->badCharacters)) {
                continue;
            }

            $good->add($piece);
        }

        $good = $good->keyBy('id');
        $goodUnique = new Collection();

        foreach ($good as $piece) {
            $existingTexts = $good->except([$piece->id])->pluck('content')->toArray();
            $unique = !$this->uniquenessService->hasDuplicates($piece->content, $existingTexts, 15, []);
            if($unique) {
                $goodUnique->add($piece);
            }
        }

        Piece::query()
            ->whereNotIn('id', $goodUnique->pluck('id')->toArray())
            ->where('keyword_id', '=', $keyword->id)
            ->delete();
    }

    private function strpos_arr($haystack, $needle) {
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $what) {
            if(($pos = strpos($haystack, $what))!==false) return $pos;
        }
        return false;
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
        $sentences = $sentences->filter(function($sentence) {
            return $this->strpos_arr($sentence, $this->stopWords) === false;
        })->toArray();

        $generatedPiece->content = implode(' ', $sentences);
    }

}
