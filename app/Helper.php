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

    public static function isTextCaps(string $text)
    {
        $text = preg_replace( '/\W/', '', $text);
        $words = explode(' ', $text);
        $qtyCaps = 0;
        foreach ($words as $word) {
            if(ctype_upper($word)) {
                $qtyCaps++;
            }
        }

        return ($qtyCaps / count($words)) > 0.3;
    }

    public static function deCapitalizeText(string $text)
    {
        $sentences = explode('. ', $text);
        foreach ($sentences as &$sentence) {
            $sentence = ucfirst(mb_strtolower($sentence));
        }

        $sentences[0] = ucfirst($sentences[0]);
        return implode('. ', $sentences);
    }

}
