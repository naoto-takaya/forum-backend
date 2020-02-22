<?php

namespace Tests\Feature;

use App\User;
use Aws\Command;
use Aws\Rekognition\Exception\RekognitionException;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ImageTest extends TestCase
{
    private $client;
    private $user;

    // 画像の節度から作成されるレベル
    private const BAN = 0;    // 登録不可
    private const NORMAL = 1; // 制限なし
    private const BlUR = 2;   // ぼかしをかけて表示

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
        $this->user = factory(User::class)->create();
        $this->client = Mockery::mock(RekognitionClient::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * 画像アップロードに成功する
     * @test
     */
    public function success_forum_rekognition()
    {
        $this->client
            ->shouldReceive('detectModerationLabels')
            ->with(
                Mockery::any()
            )
            ->andReturn([
                'ModerationLabels' => [
                    [
                        'Confidence' => 90,
                    ],
                ],
                'ModerationModelVersion' => '<string>',
            ]);
        $this->app->instance('Aws\Rekognition\RekognitionClient', $this->client);

        $response = $this->actingAs($this->user)->json('POST', route('rekognition.forums'), [
            'image' => UploadedFile::fake()->image('photo.png'),
        ]);

        $response->assertJsonFragment([
            'level' => self::NORMAL,
        ]);

        Storage::cloud()->assertExists(session()->get('image_name'));
    }

    /**
     * @test
     */
    public function success_response_rekognition()
    {
        $this->client
            ->shouldReceive('detectModerationLabels')
            ->with(
                Mockery::any()
            )
            ->andReturn([
                'ModerationLabels' => [
                    [
                        'Confidence' => 90,
                    ],
                ],
                'ModerationModelVersion' => '<string>',
            ]);
        $this->app->instance('Aws\Rekognition\RekognitionClient', $this->client);

        $response = $this->actingAs($this->user)->json('POST', route('rekognition.responses'), [
            'image' => UploadedFile::fake()->image('photo.png'),
        ]);

        $response->assertJsonFragment([
            'level' => self::NORMAL,
        ]);

        Storage::cloud()->assertExists(session()->get('image_name'));
    }

    /**
     * Rekognitionに失敗した場合, セッションへ値が保存されない
     * @test
     */
    public function faile_rekognition()
    {
        $this->client
            ->shouldReceive('detectModerationLabels')
            ->with(Mockery::any())
            ->andThrow(new RekognitionException('', new Command('')));

        $this->app->instance('Aws\Rekognition\RekognitionClient', $this->client);

        $response = $this->actingAs($this->user)->json('POST', route('rekognition.responses'), [
            'image' => UploadedFile::fake()->image('photo.png'),
        ]);
        $response
            ->assertStatus(500)
            ->assertSessionMissing(['image_name', 'confidence', 'level']);
    }
}
