<?php

namespace App\Services;

use App\Models\GeneratedPiece;
use App\Models\Keyword;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class GooglePlacesService
{

    const KEY_PARAM = 'GOOGLE_CLOUD_API_KEY';

    public static function saveKeywordData(Keyword $keyword)
    {
        $objectName = $keyword->object_name;
        $client = new Client();
        $url = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json';
        $response = $client->get($url, [
            'query' => [
                'input' => $objectName,
                'inputtype' => 'textquery',
                'key' => env(self::KEY_PARAM),
            ]
        ]);

        $result = json_decode($response->getBody()->__toString(), true);
        $placeId = $result["candidates"][0]["place_id"] ?? null;

        if ($placeId) {
            $url = 'https://maps.googleapis.com/maps/api/place/details/json';
            $response = $client->get($url, [
                'query' => [
                    'place_id' => $placeId,
                    'key' => env(self::KEY_PARAM),
                ]
            ]);
            $result = json_decode($response->getBody()->__toString(), true);
            $keyword->additional_data = [
                'website' => $result["result"]["website"] ?? null,
                'phone' => $result["result"]["international_phone_number"] ?? null,
                'photos' => $result["result"]["photos"] ?? null,
                'address' => self::getAddress($result) ?? null,
                'reviews' => $result["result"]["reviews"] ?? null,
            ];
            $keyword->save();

            $qty = min($keyword->chosenGeneratedPieces->count(), 3);
            $i = 0;
            $j = 0;
            while($i < $qty) {
                $photoData = $keyword->additional_data['photos'][$i];
                if($photoData['width'] / $photoData['height'] < 1.2) {
                    $i++;
                    continue;
                }

                $j++;
                /** @var GeneratedPiece $generatedPiece */
                $generatedPiece = $keyword->chosenGeneratedPieces->get($j);
                if(!$generatedPiece) {
                    break;
                }

                $i++;
                $url = 'https://maps.googleapis.com/maps/api/place/photo';
                $response = $client->get($url, [
                    'query' => [
                        'photo_reference' => $photoData['photo_reference'],
                        'key' => env(self::KEY_PARAM),
                        'maxwidth' => 300
                    ],
                ]);

                $fileExtension = self::getImgFileExtension($response);
                $filePath = '/uploads/'. Str::slug($objectName.'-'. $i) . $fileExtension;
                file_put_contents(
                    public_path() . $filePath,
                    $response->getBody()
                );

                $generatedPiece->image = $filePath;
                $generatedPiece->save();
            }

            return true;
        }

        return false;
    }

    private static function getAddress($result)
    {
        $address = '';

        foreach ($result["result"]["address_components"] as $address_component) {
            if ($element = array_intersect($address_component['types'], [
                'postal_town',
                'administrative_area_level_2',
                'administrative_area_level_1',
                'country',
                'postal_code'
            ])) {
                if ($element[0] == 'country') {
                    $address .= $address_component['long_name'] . ', ';
                } else {
                    $address .= $address_component['short_name'] . ', ';
                }
            }
        }

        return trim($address, ', ');
    }

    private static function getImgFileExtension(ResponseInterface $response)
    {
        $contentType = $response->getHeader('Content-Type');
        switch ($contentType) {
            case 'image/png':
                return '.png';
            default:
                return '.jpg';
        }
    }
}
