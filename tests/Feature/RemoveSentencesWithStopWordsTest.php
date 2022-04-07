<?php

namespace Tests\Feature;

use App\Models\GeneratedPiece;
use App\Services\ArticleGenerationService;
use PHPUnit\Framework\TestCase;
use function resolve;

class RemoveSentencesWithStopWordsTest extends TestCase
{
    public $testText = 'The University of St. Andrews, established in the 15th-century, is world-renowned for its academic excellence. With over 9 000 students studying there today – over 2000 of which are international students from 130 different countries around the world – it has grown to become an increasingly diverse institution.
Throughout their time at university, every new undergraduate student will have their own personal adviser who will take care of them from enrolment until graduation and provide advice on whatever they might need along the way. Students can also be sure that they will have a place on campus as long as they send in their application before the deadline: accommodation is guaranteed!
You can find more information on these and other services by visiting the website - just click below!';

    public $expectedResult = 'The University of St. Andrews, established in the 15th-century, is world-renowned for its academic excellence. With over 9 000 students studying there today – over 2000 of which are international students from 130 different countries around the world – it has grown to become an increasingly diverse institution.
Throughout their time at university, every new undergraduate student will have their own personal adviser who will take care of them from enrolment until graduation and provide advice on whatever they might need along the way. Students can also be sure that they will have a place on campus as long as they send in their application before the deadline: accommodation is guaranteed!';

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testRemoveSentencesWithStopWords()
    {
        $generatedPiece = new GeneratedPiece();
        $generatedPiece->content = $this->testText;

        /** @var ArticleGenerationService $service */
        $service = resolve(ArticleGenerationService::class);

        $service->removeSentencesWithStopWords($generatedPiece);

        $this->assertEquals(
            str_replace(["\n", ' '], '', $generatedPiece->content),
            str_replace(["\n", ' '], '', $this->expectedResult)
        );


    }
}
