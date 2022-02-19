<?php

namespace App\Services;

use App\Models\Spell;
use GuzzleHttp\Client;

class TextGenerationService {

    public static function generate($inputText, Spell $spell, $qty = 1)
    {
        $client = new Client();
        $payload = sprintf($spell->prompt, $inputText);
        if($spell->tokens == 0) {
            $tokens = round(count(explode(' ', $inputText)) * 1.5);
        } else {
            $tokens = $spell->tokens;
        }
        $apiKey = env('OPENAI_API_KEY');
        $response = $client->post(sprintf('https://api.openai.com/v1/engines/%s/completions', $spell->engine), [
            'headers' => ['Authorization' => sprintf('Bearer %s', $apiKey)],
            'json' => [
                'prompt' => $payload,
                'n' => $qty,
                'max_tokens' => $tokens, //200
                "temperature" => (float) $spell->temperature, //0.2
                "top_p" => (float) $spell->top_p, //1
                "frequency_penalty" => (float) $spell->frequency_penalty, //1
                "stop" => ['"""', 'Text:', 'Seed']
            ]
        ]);

        $result = json_decode($response->getBody()->__toString(), true);
        if($qty == 1) {
            return $result["choices"][0]["text"];
        }

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

}
