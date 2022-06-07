<?php

namespace App\Services;

use App\Helper;
use App\Models\GeneratedPiece;
use App\Models\Keyword;
use App\Models\Spell;

class FinalizingService {

    const STOP_CHARS = ['.', '!', '?'];

    public static $stopWords = [
        'https://',
        'http://',
    ];

    public static function finalizePost(Keyword $keyword)
    {
        foreach ($keyword->generatedPieces as $generatedPiece) {
            if(!in_array(substr($generatedPiece->content, -1), self::STOP_CHARS)) {
                self::finalizeGeneratedPiece($generatedPiece);
            }
        }
    }

    public static function lengthenPost(Keyword $keyword)
    {
        foreach ($keyword->generatedPieces as $generatedPiece) {
            self::finalizeGeneratedPiece($generatedPiece);
        }

        PostCompositionService::saveGeneratedPost($keyword);
    }

    public static function finalizeGeneratedPiece(GeneratedPiece $generatedPiece, $regenerate = false)
    {
        $stopSequences = ["\n", 'finish'];

        $spell = self::getFinalizeSpell();
        $spell->temperature = 0.5;
        $sourceText = trim($generatedPiece->piece->keyword->keyword . '.' . $generatedPiece->piece->heading . '. ' . $generatedPiece->content);
        $endings = TextGenerationService::generate($sourceText, $spell, 3, $stopSequences, 50);
        $trimmedEndings = [];
        foreach ($endings as $key => $ending) {
            $trimmed = self::trim($ending);
            if(Helper::words($trimmed) < 20) {
                continue;
            }

            /** @var UniquenessTestingService $uniquenessService */
            $uniquenessService = resolve(UniquenessTestingService::class);
            $duplicate = $uniquenessService->hasDuplicates($trimmed, [$generatedPiece->content], 5);
            if($duplicate) {
                continue;
            }

            if (Helper::strpos_arr($trimmed, self::$stopWords)) {
                continue;
            }

            $trimmedEndings[$key] = $trimmed;
        }

        if(!count($trimmedEndings)) {
            return self::finalizeGeneratedPiece($generatedPiece, $regenerate);
        }

        if(count($trimmedEndings) > 1) {
            $embeddings = TextGenerationService::embeddings($trimmedEndings);
            $distances = [];
            foreach ($embeddings as $key => $embedding) {
                $distances[$key] = EmbeddingDistanceService::getDistance($generatedPiece->piece->embedding, $embedding);
            }

            [$key] = array_keys($distances, max($distances));
            $selectedEnding = $trimmedEndings[$key];
        } else {
            $selectedEnding = array_pop($trimmedEndings);
        }

        $generatedPiece->content .= $selectedEnding;
        $generatedPiece->save();

        $generatedPiece->refresh();

        if($regenerate) {
            PostCompositionService::saveGeneratedPost($generatedPiece->piece->keyword);
        }

        return null;
    }

    public static function trim($ending)
    {
        $sentences = preg_split('/(\.|\!)/', $ending);
        $sentences = array_slice($sentences, 0, count($sentences) - 1);
        return implode('. ', $sentences) . '.';
    }

    public static function getFinalizeSpell()
    {
        return Spell::query()->find(13);
    }
}
