<?php

namespace App\Services;

class EmbeddingDistanceService {

    public static function getDistance($left, $right): float
    {
        $sum = 0;
        foreach ($left as $key => $number1) {
            $number2 = $right[$key];
            $sum += ($number1 - $number2) ** 2;
        }

        return sqrt($sum);

    }

    public static function getCosineSimilarity($left, $right): float
    {
        $xy = 0;
        foreach ($left as $key => $number1) {
            $number2 = $right[$key];
            $xy += $number1 * $number2;
        }

        $xSum = 0;
        foreach ($left as $number1) {
            $xSum += $number1 ** 2;
        }

        $xSum = sqrt($xSum);

        $ySum = 0;
        foreach ($right as $number1) {
            $ySum += $number1 ** 2;
        }

        $ySum = sqrt($xSum);

        return $xy / ($xSum * $ySum);
    }
}
