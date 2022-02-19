<?php

namespace App\Services;

use App\Models\ChinaUniversity;

class ArticleText2HtmlService {

    public function convert(ChinaUniversity $uni)
    {
        $matches = [];
        $generated = json_decode($uni->generated, true);

        $formatted = '';
        foreach ($generated as $header => $paragraph) {
            $original = $paragraph;
            $paragraph .= "\n";
            $splitted = preg_split('/(\n-|: -|; -|\. -|\n\d|•)/', $paragraph);
            if(count($splitted) > 1) {
                $paragraph = array_shift($splitted) . '<ul>';
                foreach ($splitted as &$item) {
                    $item = trim($item, ')(.}{:"');
                    $paragraph .= sprintf('<li>%s</li>', $item);
                }

                $paragraph .= '</ul>';
            }

            $paragraph = str_replace("\n", '<br/>', ltrim(rtrim($paragraph)));
            $paragraph = str_replace(['ENDcapitalisation', 'etc....... Thankyou!endez', '\endline\r', 'Text', 'Ends text_', ':""', '}', '{', '\"\"'], ' ', $paragraph);
            $paragraph = str_replace([2016, 2017, 2018, 2019, 2020, 2021], 2022, $paragraph);
            $paragraph = str_replace(['Central South University', 'Central Southern University', 'Central South College', 'Central South', 'University of Central South', 'University of Central-South', '%uni'], $uni->name, $paragraph);
            $paragraph = str_replace(['CSU', 'CSCU', 'SFU', 'CAU', 'CGU'], $uni->abbr, $paragraph);

            $paragraph = ltrim(rtrim($paragraph, '"'), '"');

            $paragraph = preg_replace(
                '/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/',
                '<a href="https://campuschina.org" rel="nofollow">https://campuschina.org</a>',
                $paragraph
            );

            $formatted .= $header . $paragraph;
        }

        $formatted = $this->randomizeHeaders($formatted);

        $uni->generated_html = $formatted;
        $uni->save();

        return 0;
    }

    private function randomizeHeaders($formatted)
    {
        $map = [
            '<h2>Facts To Know</h2>' => [
                '<h2>China Scholarship Council Facts</h2>',
                '<h2>Consider This</h2>',
                '<h2>Things To Consider</h2>',
                '<h2>Good To Know</h2>',
                '<h2>Some Facts About CSC Scholarships</h2>',
                '<h2>About Chinese Government Scholarships</h2>',
            ],
            '<h2>Where To Start</h2>' => [
                '<h2>First Steps</h2>',
                '<h2>Do Your Research</h2>',
                '<h2>Research Specific Scholarship Options</h2>',
                '<h2>Before Filling The Application</h2>',
                '<h2>Before Applying</h2>',
            ],
            '<h2>Who Can Apply</h2>' => [
                '<h2>Who Is Eligible</h2>',
                '<h2>Who Is Eligible For The Scholarships</h2>',
                '<h2>Who Can Apply For The Scholarships</h2>',
                '<h2>Eligible Students</h2>',
                '<h2>Are You Suitable For The CSC Scholarship</h2>',
            ],
            '<h2>How To Fill The Application Form</h2>' => [
                '<h2>Filling The Form Step By Step</h2>',
                '<h2>Filling The Form</h2>',
                '<h2>Filling The Application Form Details</h2>',
                '<h2>Application Form</h2>',
                '<h2>How To Apply</h2>',
            ],
            '<h2>How To Increase Your Chances</h2>' => [
                '<h2>Maximize The Probability Of Winning The Scholarship</h2>',
                '<h2>Maximize Your Success Chances</h2>',
                '<h2>Improve Your Chances</h2>',
                '<h2>How To Make Sure You Are Awarded With The Scholarship</h2>',
            ],
            '<h2>Time Frame</h2>' => [
                '<h2>China Scholarship Council Application Deadlines</h2>',
                '<h2>Deadlines Of The Submission And Results For China Scholarship Council</h2>',
                '<h2>When To Apply And Wait For Results</h2>',
                '<h2>China Scholarship Council Application And Results Time Frame</h2>',
                '<h2>Target Dates For Applying And Results</h2>',
            ],
        ];

        foreach ($map as $header => $newHeaders) {
            $formatted = str_replace($header, $newHeaders[array_rand($newHeaders)], $formatted);
        }

        return $formatted;
    }


}
