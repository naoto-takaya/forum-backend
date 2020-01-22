<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Comprehend;
use App\Infrastructure\Response;
use Aws\Command;
use Aws\Comprehend\ComprehendClient;
use Aws\Comprehend\Exception\ComprehendException;
use \Mockery;

class ComprehendTest extends TestCase
{
    use RefreshDatabase;

    private $client;
    private $complehend;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(ComprehendClient::class);
        $this->text = factory(Response::class)->make()->content;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    private function mock_BatchDetectSentiment($text, $lang, $result)
    {
        $this->client
            ->shouldReceive('BatchDetectSentiment')
            ->with([
                'LanguageCode' => $lang,
                'TextList' => [$text]
            ])
            ->andReturn([
                'ResultList' => [['Sentiment' => $result]]
            ]);
    }

    private function mock_detectDominantLanguage($text, $languages)
    {
        $this->client
            ->shouldReceive('detectDominantLanguage')
            ->with(['Text' => $text])
            ->andReturn([
                'Languages' =>  $languages
            ]);
    }

    private function mock_BatchDetectSentiment_throw_exception($text, $lang)
    {
        $this->client
            ->shouldReceive('BatchDetectSentiment')
            ->with([
                'LanguageCode' => $lang,
                'TextList' => [$text]
            ])
            ->andThrow(new ComprehendException('', new Command('')));
    }
    /**
     * @test
     */
    public function success_get_sentiment()
    {
        $languages = [
            ['LanguageCode' => 'en', 'Score' => 0.5], ['LanguageCode' => 'ja', 'Score' => 0.9]
        ];
        $this->mock_detectDominantLanguage($this->text, $languages);
        $this->mock_BatchDetectSentiment($this->text, 'ja', 'NEUTRAL');
        $this->comprehend = new Comprehend($this->client);

        $result = $this->comprehend->get_sentiment($this->text);

        $this->assertEquals(3, $result);
    }

    /**
     * 非対応言語など、Comprehendが行われなかった場合、nullが返却される
     * @test
     */
    public function fail_get_sentiment()
    {
        $languages = [
            ['LanguageCode' => 'la', 'Score' => 0.5]
        ];
        $this->mock_detectDominantLanguage($this->text, $languages);
        $this->mock_BatchDetectSentiment_throw_exception($this->text, 'la');
        $this->comprehend = new Comprehend($this->client);

        $result = $this->comprehend->get_sentiment($this->text);
        $this->assertEquals(null, $result);
    }
}
