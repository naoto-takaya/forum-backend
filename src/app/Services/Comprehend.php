<?php

namespace App\Services;

use Aws\Comprehend\ComprehendClient;
use Aws\Comprehend\Exception\ComprehendException;

class Comprehend
{

    private $client;

    public function __construct(ComprehendClient $client)
    {
        $this->client = $client;
    }

    /**
     * テキストの言語を判定する
     * @param str $text
     * @return array
     */
    public function get_dominant_language($text)
    {
        $languages = $this->client->detectDominantLanguage([
            'Text' => $text
        ])['Languages'];


        $top_language['Score'] = 0;
        foreach ($languages as $language) {
            if ($top_language['Score'] < $language['Score']) {
                $top_language = $language;
            }
        }

        return $top_language;
    }

    /**
     * テキストの感情分析を行う
     * @param str $text
     * @return int|null
     */
    public function get_sentiment($text)
    {
        $language = $this->get_dominant_language($text);
        try {
            $result = $this->client->BatchDetectSentiment([
                'LanguageCode' => $language['LanguageCode'],
                'TextList' => [$text],
            ]);
        } catch (ComprehendException $e) {
            return null;
        }


        $sentiment = $result['ResultList'][0]['Sentiment'];

        switch ($sentiment) {
            case 'POSITIVE':
                $sentiment = 1;
                break;
            case 'NEGATIVE':
                $sentiment = 2;
                break;
            case 'NEUTRAL':
                $sentiment = 3;
                break;
            case 'MIXED':
                $sentiment = 4;
                break;
        }

        return $sentiment;
    }
}
