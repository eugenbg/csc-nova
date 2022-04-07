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
}
