<?php

namespace App;

class Helper
{

    public static $command;

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
        $text = preg_replace('/\W/', '', $text);
        $words = explode(' ', $text);
        $qtyCaps = 0;
        foreach ($words as $word) {
            if (ctype_upper($word)) {
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

    public static function initCommandLogger($command)
    {
        self::$command = $command;
    }

    public static function log(...$args)
    {
        return self::$command->info(sprintf(...$args));
    }

    public static function csvToArray($filepath)
    {
        $csvRows = array_map('str_getcsv', file($filepath));
        $csvHeader = array_shift($csvRows);
        $data = [];
        foreach ($csvRows as $row) {
            $data[] = array_combine($csvHeader, $row);
        }

        return $data;
    }

    public static function arrayToCsv($file_name, $arr)
    {
        $has_header = false;

        foreach ($arr as $c) {

            $fp = fopen($file_name, 'a');

            if (!$has_header) {
                fputcsv($fp, array_keys($c));
                $has_header = true;
            }

            fputcsv($fp, $c);
            fclose($fp);
        }
    }
}
