<?php

namespace App\Services;

use App\Helper;
use App\Models\GeneratedPiece;
use App\Models\Keyword;
use App\Models\Spell;

class FinalizingService {

    const STOP_CHARS = ['.', '!', '?'];
    const MAX_FINALIZE_TRIES = 4;
    const MIN_WORDS_FOR_FINALIZING_DEFAULT = 20;
    const QTY_TO_GENERATE = 5;

    public static $stopWords = [
        'https://',
        'http://',
    ];

    /**
     * @var int
     */
    private static $counter = 0;

    /**
     * @var int
     */
    private static $minWordsForFinalizing = 10;

    public static function finalizePost(Keyword $keyword)
    {
        foreach ($keyword->generatedPieces as $generatedPiece) {
            if(!in_array(substr(trim($generatedPiece->content), -1), self::STOP_CHARS)) {
                self::$counter = 0;
                self::$minWordsForFinalizing = self::MIN_WORDS_FOR_FINALIZING_DEFAULT;
                self::finalizeGeneratedPiece($generatedPiece);
            }
        }
    }

    public static function lengthenPost(Keyword $keyword)
    {
        foreach ($keyword->chosenGeneratedPieces as $generatedPiece) {
            self::$counter = 0;
            self::$minWordsForFinalizing = self::MIN_WORDS_FOR_FINALIZING_DEFAULT;
            self::finalizeGeneratedPiece($generatedPiece, false, true);
        }

        PostCompositionService::saveGeneratedPost($keyword);
    }

    public static function finalizeGeneratedPiece(
        GeneratedPiece $generatedPiece,
        $regenerate = false,
        $lengthening = false
    ) {
        if(self::$counter >= self::MAX_FINALIZE_TRIES || self::$minWordsForFinalizing == 0) {
            $generatedPiece->content .= '.';
            $generatedPiece->save();
            return;
        }

        self::$counter++;

        $stopSequences = ["\n", 'finish'];

        $spell = self::getFinalizeSpell();
        $spell->temperature = 0.5;
        $keyword = trim($generatedPiece->piece->keyword->keyword);
        $headingOfOriginalPiece = trim($generatedPiece->piece->heading);
        $sourceText = $keyword . '. ' . $headingOfOriginalPiece . '. ' . $generatedPiece->content;
        $sourceText = str_replace("\n", '. ', $sourceText);
        $endings = TextGenerationService::generate($sourceText, $spell, self::QTY_TO_GENERATE, $stopSequences, 50);
        $trimmedEndings = [];
        foreach ($endings as $key => $ending) {
            $trimmed = self::trim($ending);
            $words = Helper::words($trimmed);
            if($words < self::$minWordsForFinalizing || $words == 0) {
                continue;
            }

            if(strpos($trimmed, '|')) {
                continue;
            }

            /** @var UniquenessTestingService $uniquenessService */
            $uniquenessService = resolve(UniquenessTestingService::class);
            $duplicate = $uniquenessService->hasDuplicates($trimmed, [$generatedPiece->content], 5);

            //has keyword in the
            if(strpos($trimmed, $generatedPiece->piece->keyword->keyword) !== false
                || strpos($trimmed, $generatedPiece->piece->heading) !== false
            ) {
                $duplicate = true;
            }
            if($duplicate) {
                continue;
            }

            if (Helper::strpos_arr($trimmed, self::$stopWords)) {
                continue;
            }

            $trimmedEndings[$key] = $trimmed;
        }

        if(!count($trimmedEndings) && $lengthening) {
            return null;
        }

        if(!count($trimmedEndings)) {
            self::decreaseMinWordsForFinalizing();
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
        $sentences = preg_split('/((?<!st)\.|\!)/i', $ending);
        $sentences = array_slice($sentences, 0, count($sentences) - 1);
        return implode('. ', $sentences) . '.';
    }

    public static function getFinalizeSpell()
    {
        return Spell::query()->find(13);
    }

    private static function decreaseMinWordsForFinalizing()
    {
        self::$minWordsForFinalizing -= 5;
    }
}
