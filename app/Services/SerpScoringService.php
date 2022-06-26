<?php

namespace App\Services;

use App\Models\Serp;
use Illuminate\Support\Collection;

class SerpScoringService {

    const RANKING_FACTORS = [
        self::FACTOR_DA,
        self::FACTOR_LINKS,
        self::FACTOR_POSITION,
        self::FACTOR_WORDS,
        self::FACTOR_DISTANCE_KEYWORD_TO_SERP_TITLE,
    ];

    const THE_LESS_THE_BETTER = [
        self::FACTOR_DA => true,
        self::FACTOR_LINKS => true,
        self::FACTOR_POSITION => true,
        self::FACTOR_WORDS => false,
        self::FACTOR_DISTANCE_KEYWORD_TO_SERP_TITLE => true,
    ];

    const FACTOR_WEIGHTS = [
        self::FACTOR_DA => 0.5,
        self::FACTOR_LINKS => 0.5,
        self::FACTOR_POSITION => 0.5,
        self::FACTOR_WORDS => 1,
        self::FACTOR_DISTANCE_KEYWORD_TO_SERP_TITLE => 5,
    ];

    const FACTOR_DA = 'da';
    const FACTOR_LINKS = 'links';
    const FACTOR_POSITION = 'position';
    const FACTOR_WORDS = 'words';
    const FACTOR_DISTANCE_KEYWORD_TO_SERP_TITLE = 'distance';
    const MIN_WORDS = 250;
    const BLACKLISTED_URLS = [
        'wemakescholars.com'
    ];

    public $valuesByFactor = [];

    public function rank(Collection $serps, $topX = 5)
    {
        $serps = $this->filterByWords($serps);
        $serps = $this->filterByUrl($serps);
        $this->saveScoringData($serps);
        $scores = [];
        foreach ($serps as $serp) {
            $scores[$serp->id] = [];
            foreach (self::RANKING_FACTORS as $factor) {
                $value = $serp->$factor ?? $this->$factor($serp);
                $max = max($this->valuesByFactor[$factor]);
                $min = min($this->valuesByFactor[$factor]);
                $range = $max - $min;
                if(self::THE_LESS_THE_BETTER[$factor]) {
                    $position = $max - $value;
                } else {
                    $position = $value - $min;
                }

                if($range == 0) { //if the data is corrupted
                    $score = 1;
                } else {
                    $score = $position / $range;
                }
                $scores[$serp->id][$factor] = $score * self::FACTOR_WEIGHTS[$factor];
            }
        }

        $scoreSums = [];
        foreach ($scores as $serpId => $scoresArray) {
            $scoreSums[$serpId] = array_sum($scoresArray);
        }

        if(!count($scoreSums)) {
            return null;
        }

        arsort($scoreSums);
        $winners = array_slice(array_keys($scoreSums), 0, $topX);
        return $winners;
    }

    private function saveScoringData(Collection $serps)
    {
        $scoreBySerpId = [];
        foreach ($serps as $serp) {
            $scoreBySerpId[$serp->id] = [];
            foreach (self::RANKING_FACTORS as $factor) {
                $this->valuesByFactor[$factor][$serp->id] = $serp->$factor ?? $this->$factor($serp);
                $scoreBySerpId[$serp->id][$factor] = $serp->$factor ?? $this->$factor($serp);
            }
        }

        /** @var Serp $serp */
        foreach ($serps as $serp) {
            $serp->scores = $scoreBySerpId[$serp->id];
            $serp->save();
        }
    }

    public function words($serp)
    {
        $words = 0;
        foreach ($serp->pieces as $piece) {
            $words += $piece->words();
        }

        return $words;
    }

    public function distance(Serp $serp)
    {
        return EmbeddingDistanceService::getDistance($serp->title_embedding, $serp->keyword->embedding);
    }

    private function filterByWords(Collection $serps)
    {
        return $serps->filter(function (Serp $serp) {
            $serp->words = $this->words($serp);
            $serp->save();
            return $serp->words > self::MIN_WORDS;
        });
    }

    private function filterByUrl(Collection $serps)
    {
        return $serps->filter(function (Serp $serp) {
            $good = true;
            foreach (self::BLACKLISTED_URLS as $blackListedUrl) {
                if(str_contains($serp->url, $blackListedUrl)) {
                    $good = false;
                }
            }

            return $good;
        });
    }
}
