<?php

namespace App\Services;

use App\Models\Spell;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class TextGenerationService {

    const DEFAULT_STOP_SEQUENCES = ['"""', 'Text:', 'Seed'];

    public static function getClient()
    {
        $apiKey = env('OPENAI_API_KEY');
        return new Client([
            'headers' => ['Authorization' => sprintf('Bearer %s', $apiKey)],
        ]);
    }

    public static function generate($inputText, Spell $spell, $qty = 1, $stopSequences = [])
    {
        $payload = sprintf($spell->prompt, $inputText);
        if($spell->tokens == 0) {
            $tokens = round(count(explode(' ', $inputText)) * 4);
        } else {
            $tokens = $spell->tokens;
        }

        if(!count($stopSequences)) {
            $stopSequences = self::DEFAULT_STOP_SEQUENCES;
        }

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
        $promises = [];
        foreach ($texts as $id => $text) {
            $promises[$id] = self::getClient()->postAsync(sprintf('https://api.openai.com/v1/engines/text-similarity-babbage-001/embeddings'), [
                'json' => [
                    'input' => $text
                ]
            ]);
        }

        $results = [];
        $responses = Promise\all($promises)->wait();
        foreach ($responses as $id => $response) {
            $data = json_decode($response->getBody()->__toString(), true);
            $results[$id] = $data['data']['0']['embedding'];
        }

        return $results;
    }

}
