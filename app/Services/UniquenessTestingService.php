<?php

namespace App\Services;

class UniquenessTestingService
{
    const HASH_DIVIDER = 25;

    private $search_text = null;

    private $stop_words = [];

    private $stop_symbols = [];


    private $shingle_length = 2;

    private $use_divider = false;

    public function __construct($options = [])
    {
        if (isset($options['stop_words'])) {
            $this->setStopWords($options['stop_words']);
        }

        if (isset($options['stop_symbols'])) {
            $this->setStopSymbols($options['stop_symbols']);
        }
    }

    /**
     * Установить длины шингла
     * @param $shingle_length
     */
    public function setShingleLength($shingle_length)
    {
        $this->shingle_length = $shingle_length;
    }

    /**
     * Получить длину шингла
     * @return int
     *
     */
    public function getShingleLength()
    {
        return $this->shingle_length;
    }

    /**
     * Установить список стоп-слов
     * @param $stop_words
     */
    public function setStopWords($stop_words)
    {
        $stop_words = is_array($stop_words) ? $stop_words : explode(',', $stop_words);

        foreach ($stop_words as &$word) {
            $word = mb_strtolower($word);
        }

        $this->stop_words = array_unique($stop_words);
    }

    /**
     * Получить список стоп-слов
     * @return array
     */
    public function getStopWords()
    {
        return $this->stop_words;
    }

    /**
     * Установить список стоп-символов
     * @param $stop_symbols
     */
    public function setStopSymbols($stop_symbols)
    {
        $stop_symbols = is_array($$stop_symbols) ? $stop_symbols : explode(',', $stop_symbols);

        foreach ($stop_symbols as $i => $symbol) {
            if (empty($symbol)) {
                unset($stop_symbols[$i]);
            }
        }

        $this->stop_symbols = $stop_symbols;
    }

    /**
     * Получить список стоп-символов
     * @return array
     */
    public function getStopSymbols()
    {
        return $this->stop_symbols;
    }

    /**
     * Установить анализируемый текст
     * @param $text
     */
    public function setSearchText($text)
    {
        $this->search_text = $text;
    }

    public function getSearchText()
    {
        return $this->search_text;
    }

    /**
     * Выполнение поиска неточных совпадений текста среди набора данных из источника данных
     * @param $text1
     * @param $text2
     * @param null $shingleLength
     * @return array|false|string
     */
    public function run($text1, $text2, $shingleLength = null)
    {
        if($shingleLength) {
            $this->shingle_length = $shingleLength;
        }

        $text1 = $this->canonizeText($text1);
        $text2 = $this->canonizeText($text2);
        $shingles1 = $this->populateShingles($text1);
        $shingles2 = $this->populateShingles($text2);

        return $this->compareData($shingles1, $shingles2);
    }

    public function hasDuplicates($testedText, $existingTexts, $threshold = 20, $stopWords = [])
    {
        $this->setStopWords($stopWords);
        foreach ($existingTexts as $existingText) {
            $rate = $this->run($testedText, $existingText);
            if($rate > $threshold) {
                return true;
            }
        }

        return false;
    }

    /**
     * Сравниваем пачку данных с исходным текстом
     * @param $shingles1
     * @param $shingles2
     */
    private function compareData($shingles1, $shingles2)
    {
        $intersect = array_intersect($shingles1, $shingles2);
        $merge = array_unique(array_merge($shingles1, $shingles2));

        $diff = round((count($intersect) / count($merge)) / 0.01, 2);

        return $diff;
    }

    /**
     * Подготавливаем текст
     * Для этого:
     * - удаляем стоп-символы
     * - удаляем стоп-слова
     * - приводим все к нижнему регистру
     *
     * @see http://habrahabr.ru/post/65944
     *
     * @param $text
     * @return mixed|string
     */
    private function canonizeText($text)
    {
        $text = trim($text);
        $text = mb_strtolower($text);
        $text = $this->stripHtmlTags($text);
        $text = $this->replaceStopSymbols($text);
        $text = $this->replaceStopWords($text);
        $text = $this->clearMultiSpaces($text);
        $text = trim($text);
        return $text;
    }


    /**
     * Очищаем текст от html-тагов
     * @see http://www.php.net/manual/ru/function.strip-tags.php#68757
     */
    private function stripHtmlTags($text)
    {
        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@' // Strip multi-line comments including CDATA
        );
        return preg_replace($search, '', $text);
    }

    /**
     * Замена стоп-символов в тексте
     * @param $text
     * @return mixed
     */
    private function replaceStopSymbols($text)
    {
        $stop_symbols = $this->getStopSymbols();

        // Если не указан список спец. символов, то заменяем убираем из текста все что не буква/цифра/пробел
        if (empty($stop_symbols)) {
            $pattern = '/[^a-zA-Z 0-9а-яА-Я]+/';
            $pattern .= 'u';

            return preg_replace($pattern, ' ', $text);
        } else {
            //@todo напистаь обработчик для замены стоп символов
        }

        return $text;
    }


    /**
     * Замена стоп-слов в тексте
     * @param $text
     * @return mixed
     */
    private function replaceStopWords($text)
    {
        $stop_words = $this->getStopWords();

        if (!empty($stop_words)) {
            $pattern = '/\b(' . implode('|', $this->stop_words) . ')\b/';

            // @todo возможно стоит 1 раз определить признак и использовать уже значение переменной в коде
            $pattern .= 'u';

            return preg_replace($pattern, '', $text);
        }

        return $text;
    }

    /**
     * Заменяем все разделите встречающиеся 1 и более раз на пробел
     * @param $text
     * @return mixed
     */
    private function clearMultiSpaces($text)
    {
        $pattern = '/\s+/';
        return preg_replace($pattern, ' ', $text);
    }

    /**
     * Формируем набор шинглов
     * @param $text
     * @return array
     */
    public function populateShingles($text)
    {
        $elements = explode(" ", $text);

        $count = count($elements);

        $shingle_length = $this->getShingleLength();

        // В случае, если количество слов в тексте меньше минимальной длины шингла
        // мы устанавливаем длину шингла равную этому значению
        if ($count > $this->shingle_length) {
            $count = $count - $this->shingle_length + 1;
        } else {
            $shingle_length = $count;
        }

        $shingles = [];
        $shingles_hash = [];

        for ($i = 0; $i < $count; $i++) {
            $shingle = implode(" ", array_slice($elements, $i, $shingle_length));
            $shingles[] = $shingle;
            $shingles_hash[] = crc32($shingle);
        }

        return $shingles_hash;
    }

}
