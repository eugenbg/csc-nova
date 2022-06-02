<?php

namespace App\Services;

use App\Models\GeneratedPiece;
use App\Models\Keyword;
use App\Models\Piece;
use App\Models\Serp;
use App\Models\Spell;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class HeadingGenerationService {

    /**
     * @var UniquenessTestingService
     */
    private $uniquenessService;

    const HEADING_SPELL_ID = 12;
    const HEADINGS_GENERATE_QTY = 5;
    const MAX_WORDS = 10;

    /**
     * @var array
     */
    private $embeddings = [];

    public function __construct(UniquenessTestingService $uniquenessService)
    {
        $this->uniquenessService = $uniquenessService;
    }

    public function generateHeading(GeneratedPiece $generatedPiece, $otherHeadings = [])
    {
    }

    public function generateHeadings(Serp $serp)
    {
        $allHeadings = [];
        $originalHeadings = [];
        $generatedPieces = $serp->generatedPieces->filter(function (GeneratedPiece  $generatedPiece) {
            return $generatedPiece->chosen;
        });

        foreach ($generatedPieces as $generatedPiece) {
            $spell = Spell::query()->find(self::HEADING_SPELL_ID);
            $generatedHeadings = array_map('strtolower', TextGenerationService::generate(
                $generatedPiece->heading, $spell,
                self::HEADINGS_GENERATE_QTY,
                ["\n", '"""']
            ));

            $generatedHeadings = array_map('trim', $generatedHeadings);
            $generatedHeadings = array_filter(array_unique($generatedHeadings));

            if (($key = array_search(strtolower($generatedPiece->heading), $generatedHeadings)) !== false) {
                unset($generatedHeadings[$key]);
            }

            $generatedPiece->generated_headings = $generatedHeadings;
            $generatedPiece->save();

            $allHeadings = array_merge($allHeadings, $generatedHeadings);
            $originalHeadings[] = $generatedPiece->piece->heading;
        }

        $allHeadings = array_unique($allHeadings);
        $allHeadings = array_merge($allHeadings, $originalHeadings);
        $allHeadings = array_combine($allHeadings, $allHeadings);

        $this->embeddings = TextGenerationService::embeddings($allHeadings);
    }

    public function chooseHeadings(Serp $serp)
    {
        $generatedPieces = $serp->generatedPieces->filter(function (GeneratedPiece  $generatedPiece) {
            return $generatedPiece->chosen;
        });

        $otherHeadings = [];
        foreach ($generatedPieces as $generatedPiece) {
            $generatedHeadings = $generatedPiece->generated_headings;
            $generatedHeadings = array_filter($generatedHeadings, function($heading) use ($otherHeadings) {
                return !in_array($heading, $otherHeadings);
            });

            $payload = [];
            foreach ($generatedHeadings as $key => $generatedHeading) {
                $distanceHeading = EmbeddingDistanceService::getDistance(
                    $this->embeddings[$generatedPiece->heading],
                    $this->embeddings[$generatedHeading]
                );

                $distanceContent = EmbeddingDistanceService::getDistance(
                    $generatedPiece->embedding,
                    $this->embeddings[$generatedHeading]
                );

                if(count(explode(' ', $generatedHeading)) > self::MAX_WORDS) {
                    continue;
                }

                $payload[] = [
                    'heading' => $generatedHeading,
                    'distance_original_heading' => $distanceHeading,
                    'distance_content' => $distanceContent,
                    'sum_distance' => $distanceHeading + $distanceContent / 2
                ];
            }

            $sorted = Arr::sort($payload, 'sum_distance');
            $winner = array_shift($sorted);
            $otherHeadings[] = $winner['heading'];
            $generatedPiece->chosen_heading = ucwords($winner['heading']);
            $generatedPiece->save();
        }
    }

}
