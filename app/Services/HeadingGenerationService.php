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

    const HEADING_SPELL_ID = 11;
    const HEADINGS_GENERATE_QTY = 11;
    const MAX_WORDS = 10;

    public function __construct(UniquenessTestingService $uniquenessService)
    {
        $this->uniquenessService = $uniquenessService;
    }

    public function generateHeading(GeneratedPiece $piece, $otherHeadings = [])
    {
        $spell = Spell::query()->find(self::HEADING_SPELL_ID);
        $generatedHeadings = array_map('strtolower', TextGenerationService::generate(
            $piece->content, $spell,
            self::HEADINGS_GENERATE_QTY,
            ["\n", '"""']
        ));

        $generatedHeadings = array_unique($generatedHeadings);

        if (($key = array_search(strtolower($piece->heading), $generatedHeadings)) !== false) {
            unset($generatedHeadings[$key]);
        }

        $headingEmbeddings = TextGenerationService::embeddings($generatedHeadings);
        $otherHeadingEmbeddings = TextGenerationService::embeddings($otherHeadings);
        $originalHeadingEmbedding = TextGenerationService::embeddings([$piece->heading])[0];

        $payload = [];
        foreach ($generatedHeadings as $key => $generatedHeading) {
            $distanceHeading = EmbeddingDistanceService::getDistance($originalHeadingEmbedding, $headingEmbeddings[$key]);
            $distanceContent = EmbeddingDistanceService::getDistance($piece->embedding, $headingEmbeddings[$key]);
            if($distanceHeading < 0.3 || count(explode(' ', $generatedHeading)) > self::MAX_WORDS) { //it means it's basically the same heading
                continue;
            }

            foreach ($otherHeadings as $ohKey => $otherHeading) {
                $distanceOtherHeading = EmbeddingDistanceService::getDistance(
                    $originalHeadingEmbedding,
                    $otherHeadingEmbeddings[$ohKey]
                );

                if($distanceOtherHeading < 0.4) {
                    continue(2);
                }
            }

            $payload[] = [
                'heading' => $generatedHeading,
                'distance_original_heading' => $distanceHeading,
                'distance_content' => $distanceContent,
                'sum_distance' => $distanceHeading + $distanceContent
            ];
        }

        $sorted = Arr::sort($payload, 'sum_distance');
        return $sorted[0]['heading'];
    }

}
