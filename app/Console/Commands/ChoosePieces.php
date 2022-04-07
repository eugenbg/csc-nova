<?php

namespace App\Console\Commands;

use App\Models\GeneratedPiece;
use App\Models\Keyword;
use App\Models\Piece;
use App\Models\Post;
use App\Models\Category;
use App\Models\ChinaUniversity;
use App\Models\Serp;
use App\Models\Slug;
use App\Services\TextGenerationService;
use App\Services\UniquenessTestingService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Phpml\Clustering\KMeans;


class ChoosePieces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'choose-pieces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $calculated = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $keywords = Keyword::query()
            ->has('pieces')
            ->limit(1)
            ->get();

        foreach ($keywords as $keyword) {
            //$this->chooseBestPiecesAndChangeSimilar($keyword);
            $this->choosePiecesByBestArticle($keyword);
        }
    }

    private function getClosestPieceIdsMap(Serp $serp)
    {
        $pieces = $serp->pieces;
        $otherPieces = $serp->keyword->pieces->filter(function (Piece $piece) use ($serp) {
            return $piece->serp_id !== $serp->id;
        });

        $result = [];
        foreach ($pieces as $id => $piece) {
            foreach ($otherPieces as $otherPiece) {
                $distance = $this->getDistance($piece, $otherPiece);
                $result[$piece->id][$otherPiece->id] = $distance;
            }
        }

        $minimalDistances = [];
        foreach($result as $pieceId => $distances) {
            $minimalDistances[$pieceId] = [
                'piece_id' => array_search(min($distances), $distances),
                'distance' => min($distances)
            ];
        }

        $sorted = Arr::sort($minimalDistances, 'distance');
        $qty = floor(count($sorted) / 2);

        $result = [];
        $i = 0;
        foreach ($sorted as $fromPiece => $item) {
            $result[$fromPiece] = $item['piece_id'];
            $i++;
            if($i == $qty) { break; }
        }

        return $result;
    }

    /**
     * @param Piece|GeneratedPiece $piece
     * @param Piece|GeneratedPiece $otherPiece
     * @return float
     */
    protected function getDistance($piece, $otherPiece)
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

    private function chooseBestPiecesAndChangeSimilar(Keyword $keyword)
    {
        $serp = $this->getBestSerpByKeyword($keyword);
        $closestMap = $this->getClosestPieceIdsMap($serp);
        $closestPieces = Piece::query()
            ->whereIn('id', $closestMap)
            ->get()
            ->keyBy('id');

        $this->info('ORIGINAL ARTICLE');
        foreach ($serp->pieces as $piece) {
            $this->info('--------');
            $this->info('--------');
            $this->info($piece->heading);
            $this->info('--------');
            $this->info($piece->content);
        }

        $this->info('CHANGED ARTICLE');
        foreach ($serp->pieces as $piece) {
            if(isset($closestMap[$piece->id])) {
                $changedPieceId = $closestMap[$piece->id];
                $changedPiece = $closestPieces->get($changedPieceId);
                $this->info('--------');
                $this->info('---CHANGED---');
                $this->info($changedPiece->heading);
                $this->info('--------');
                $this->info($changedPiece->content);
                $changedPiece->chosen = true;
                $changedPiece->save();
            } else {
                $this->info('--------');
                $this->info('--------');
                $this->info($piece->heading);
                $this->info('--------');
                $this->info($piece->content);
                $piece->chosen = true;
                $piece->save();
            }
        }
    }
}
