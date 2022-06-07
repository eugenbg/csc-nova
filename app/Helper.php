<?php

namespace App;

class Helper {

    public static function words(string $text): int
    {
        return count(explode(' ', $text));
    }

    public static function strpos_arr($haystack, $needle)
    {
        if (!is_array($needle)) $needle = array($needle);
        foreach ($needle as $what) {
            if (($pos = strpos($haystack, $what)) !== false) return $pos;
        }
        return false;
    }

}
