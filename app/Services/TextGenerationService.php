<?php

namespace App\Services;

use App\Helper;
use App\Models\Spell;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Arr;

class TextGenerationService {

    const DEFAULT_STOP_SEQUENCES = ['"""', 'Text:', 'Seed', 'seed'];
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
            $stopSequences = self::DEFAULT_STOP_SEQUENCES;
        }

        try {
            $response = self::getClient()->post(sprintf('https://api.openai.com/v1/engines/%s/completions', $spell->engine), [
                'json' => [
                    'prompt' => $payload,
                    'n' => $qty,
                    'max_tokens' => $tokens, //200
                    "temperature" => (float) $spell->temperature, //0.2
                    "top_p" => (float) $spell->top_p, //1
                    "frequency_penalty" => (float) $spell->frequency_penalty, //1
                    "stop" => $stopSequences
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

    public function getEngines()
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
