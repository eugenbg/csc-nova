<?php

namespace App\Services;

use App\Helper;
use App\Models\Spell;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Arr;

class TextGenerationService {

    //const DEFAULT_STOP_SEQUENCES = ['"""', 'Text:', 'Seed', 'seed'];
    const DEFAULT_STOP_SEQUENCES = ['"""', 'Text:', 'TEXT:', 'VERBOSE'];
    const ERROR_TYPE_SPENT = 'insufficient_quota';
    const ERROR_TYPE_REQUESTS_LIMIT = 'requests';

    public static function getClient()
    {
        $apiKey = env('OPENAI_API_KEY');
        return new Client([
            'headers' => ['Authorization' => sprintf('Bearer %s', $apiKey)],
        ]);
    }

    public static function generate($inputText, Spell $spell, $qty = 1, $stopSequences = [], $overrideTokensLength = null)
    {
        $payload = sprintf($spell->prompt, $inputText);
        if($spell->tokens == 0) {
            $tokens = min(2049, round(count(explode(' ', $inputText)) * 3));
        } else {
            $tokens = $spell->tokens;
        }

        if($overrideTokensLength) {
            $tokens = $overrideTokensLength;
        }

        if(!count($stopSequences)) {
            if($spell->stop_sequences) {
                $stopSequences = json_decode($spell->stop_sequences);
            } else {
                $stopSequences = self::DEFAULT_STOP_SEQUENCES;
            }
        }

        try {
            $response = self::getClient()->post('https://api.openai.com/v1/completions', [
                'json' => [
                    'prompt' => $payload,
                    'model' => $spell->engine,
                    'n' => $qty,
                    'max_tokens' => $tokens, //200
                    "temperature" => (float) $spell->temperature, //0.2
                    "top_p" => (float) $spell->top_p, //1
                    "frequency_penalty" => (float) $spell->frequency_penalty, //1
                    "stop" => $stopSequences,
                    "logprobs" => 2,
                    "logit_bias" => [
                        32541 => -10,//TEXT
                        40383 => -10,//TEXT
                        23578 => -3,//END
                        10619 => -3,//END
                        12915 => -3,//END
                        886 => -3,//END
                        5268 => -3,//END
                        21017 => -20,//###
                        1 => -100, //"
                        2014 => -100, //.)
                        11504 => -100, //    
                        4603 => -100, //    
                        5624 => -100, //  
                        47654 => -100, //  
                        10 => -100, //+
                        1343 => -100, //+
                        46904 => -100, //-->
                        1911 => -100, //"
                        366 => -100, //"
                        526 => -100, //"
                        27 => -100, //< - no html
                        1279 => -100, //< - no html
                        29 => -100, //> - no html
                        1875 => -100, //> - no html
                        7359 => -100, // </ - no html
                        43734 => -100, //>) - no html
                        14 => -100, // / - no html
                        5450 => -100, // / - https
                        685 => -100, // [
                        58 => -100, // [
                        60 => -100, // ]
                        2361 => -100, // ]
                        8183 => -100, // ]
                        3740 => -100, // / - https
                        4023 => -100, // / - http
                        2638 => -100, // / - http
                        2503 => -100, // / - www
                        7324 => -100, // / - www
                        12976 => -100, // / - click
                        3904 => -100, // / - click
                        6 => -100, //'
                        705 => -100, //'
                        2 => -100, //#
                        1303 => -100, //#
                        2235 => -100, //##
                        22492 => -100, //##
                        25970 => -100, //.</
                        24618 => -100, //.</
                        29847 => -100, //.</
                        11037 => -100, //.</
                        960 => -100, //—
                        11709 => -100, //}}
                        28725 => -100, //><
                        6927 => -100, //><
                        3556 => -100, //</
                        1220 => -100, // /
                        2625 => -100, //="
                        5320 => -100, //></
                        12240 => -100, //">
                        5299 => -100, //"~
                        93 => -100, //~
                        31034 => -100, // (~
                        38155 => -100, // (~
                        4008 => -100, // ((
                        15306 => -100, // ((
                        3187 => -100, // visit
                        48725 => -100, // !:
                        1782 => -100, // }
                        92 => -100, // }
                        90 => -100, // {
                        1391 => -100, // {
                        3052 => -100, // website
                        401 => -100, //  com
                        19618 => -100, //  disclaimer
                        37592 => -100, //  disclaimer
                        35699 => -100, //  disclaimer
                        9 => -100, //  *
                        1635 => -100, //  *
                        31 => -100, //  @
                        16 => -2, //1
                        352 => -2, //1
                        17 => -2, //2
                        362 => -2, //2
                        18 => -2, //3 - to prevent lists
                        513 => -2, //3 - to prevent lists
                    ]
                ]
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            $data = json_decode($exception->getResponse()->getBody()->__toString(), true);
            if(($data['error']['type'] ?? null) == self::ERROR_TYPE_SPENT) {
                OpenaiKeyService::switchToNextKey();
                return self::generate($inputText, $spell, $qty, $stopSequences, (int) ($tokens * 0.8));
            }

            $resultString = $exception->getResponse()->getBody()->__toString();
            if(str_contains($resultString, 'Please reduce your prompt; or completion length')) {
                return self::generate($inputText, $spell, $qty, $stopSequences, (int) ($tokens * 0.8));
            }

            throw $exception;
        }

        $result = json_decode($response->getBody()->__toString(), true);

        $texts = [];
        foreach ($result["choices"] as $choice) {
            $texts[] = $choice['text'];
        }

        return $texts;
    }

    public static function getEngines()
    {
        $client = new Client();
        $apiKey = env('OPENAI_API_KEY');
        $response = $client->get('https://api.openai.com/v1/engines', [
            'headers' => ['Authorization' => sprintf('Bearer %s', $apiKey)]
        ]);
        return json_decode($response->getBody()->__toString(), true);
    }

    public static function embeddings(array $texts)
    {
        if(!count($texts)) {
            return [];
        }

        try {
            $response = self::getClient()->post(sprintf('https://api.openai.com/v1/engines/text-similarity-babbage-001/embeddings'), [
                'json' => [
                    'input' => array_values($texts)
                ]
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $exception) {
            $data = json_decode($exception->getResponse()->getBody()->__toString(), true);
            if(($data['error']['type'] ?? null) == self::ERROR_TYPE_SPENT) {
                OpenaiKeyService::switchToNextKey();
                return self::embeddings($texts);
            }

            if($data["error"]["type"] == self::ERROR_TYPE_REQUESTS_LIMIT) {
                sleep(60);
                return self::embeddings($texts);
            }

            Helper::log('unknown error during getting embeddings %s', $exception->getMessage());
            Helper::log('response: %s', $exception->getResponse()->getBody()->__toString());
        }

        $result = json_decode($response->getBody()->__toString(), true);
        return array_combine(array_keys($texts), Arr::pluck($result["data"], 'embedding'));
    }

}
