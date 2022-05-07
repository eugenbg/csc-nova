<?php

namespace Tests\Feature;

use App\Models\GeneratedPiece;
use App\Services\ArticleGenerationService;
use App\Services\EmbeddingDistanceService;
use App\Services\TextGenerationService;
use PHPUnit\Framework\TestCase;
use function resolve;

class EmbeddingsTest extends TestCase
{
    public $sourceText = 'Although named after the country now known as Egypt, and apparently one of the oldest known breeds, the Abyssinian did not originate from Egypt at all. Studies by geneticists show that the most likely origins for this breed are the coast of the Indian Ocean and parts of Southeast Asia. They gained the name due to the first of these cats imported to the UK being brought from Egypt, and they do closely resemble cats depicted in ancient Egyptian paintings and sculptures. They were first shown in the UK in 1871 at Crystal Palace and it seems that they were brought back to the UK following the departure of British troops from Abyssinia in 1868.';
    public $texts = [
        "Despite being a long-known and a one of the oldest cat breeds out there, Abyssinian breed has got some white spots on it's origin. Some scientists claim that these cats hail from Ethiopia, long before known as Abyssinia. Other researchers say that the breed originates from England, and they are called like that just because of their oriental look. Well actually, their appearance is close to the ones of ancient egyptian cats, those elegant yet muscular felines with fine neck, large ears and charming almond-ish eyes “highlighted” by a dark line. Even today they bear the look of wild african steppe-cat Felis Lybica - an ancestor to all domesticated cats.",
        'The origin of the Abyssinian is shrouded in mystery. Early cat books do not shed much light on the history of this breed because there were few or no records kept. It was thought that the first cat was brought to England by a British soldier, in 1868, after the English army had fought in Abyssinia (present day Ethiopia). It is believed that this cat, named "Zula" is the founder of the Abyssinian line',
        'The Abyssinian cat requires a lot of room to exercise and play, and as they enjoy climbing and being up high, extensive cat trees or an environment that gives them an outlet for this behaviour are very much a requirement. Highly inquisitive, they are also trainable using positive reinforcement methods, and it is recommended that you use training and/or enrichment games to have your Abyssinian work for some of their food to keep their brains and bodies active.',
        'Urban legend says that Bobtails are the result of a cross breeding between a domestic tabby cat and a wild bobcat. The unusual tail is actually the result of a random spontaneous genetic mutation within the domestic cat population, and may be related to the Manx gene, which is also dominant.[1] Yodie, a short-tailed brown tabby male, was mated with a seal-point Siamese female to create the American Bobtail original bloodline. Most of the early bloodlines have died out',
        'Dachshunds are scent hound dogs bred to hunt badgers and other tunneling animals, rabbits, and foxes. Hunters even used packs of Dachshunds to trail wild boar. Today their versatility makes them excellent family companions, show dogs, and small-game hunters.',
    ];

    /**
     * расстояние должно уменьшаться по мере увеличения порядкового номера текста
     * EmbeddingDistanceService - расчет расстояния
     * $result = TextGenerationService::embeddings($payload); - сгенерить embeddings
     * @return void
     */
    public function testEmbeddingsAccuracy()
    {
        [$sourceEmbedding] = TextGenerationService::embeddings([$this->sourceText]);
        $controlEmbeddings = TextGenerationService::embeddings($this->texts);

        foreach ($controlEmbeddings as $key => $controlEmbedding) {
            $distance = EmbeddingDistanceService::getDistance($sourceEmbedding, $controlEmbedding);
            echo sprintf("text %s distance %s\n", $key, $distance);
        }

        foreach ($controlEmbeddings as $key => $controlEmbedding) {
            $simScore = EmbeddingDistanceService::getCosineSimilarity($sourceEmbedding, $controlEmbedding);
            echo sprintf("text %s cosine similarity %s\n", $key, $simScore);
        }
    }
}
