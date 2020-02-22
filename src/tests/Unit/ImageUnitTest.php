<?php

namespace Tests\Unit;

use App\SharedServices\ImageSharedService;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ImageUnitTest extends TestCase
{
    private $client;
    private $image_shared_service;
    private $request;

    // 画像の節度から作成されるレベル
    private const BAN = 0;    // 登録不可
    private const NORMAL = 1; // 制限なし
    private const BlUR = 2;   // ぼかしをかけて表示

    public function setup(): void
    {
        parent::setUp();
        Storage::fake('s3');
        $this->client = Mockery::mock(RekognitionClient::class);
        $this->image_shared_service = new ImageSharedService($this->client);
        $this->request = new FormRequest();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @test
     */
    public function success_rekognition_get_BAN_level()
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
                    [
                        'Confidence' => 94.9,
                    ],
                ],
                'ModerationModelVersion' => '<string>',
            ]);

        $this->request->image = UploadedFile::fake()->image('photo.png');
        $image_info = $this->image_shared_service->rekognition_forum_image($this->request);
        $this->assertNotEmpty($image_info['image_name']);
        $this->assertEquals(self::BAN, $image_info['level']);
        $this->assertEquals(94.9, $image_info['confidence']);
        Storage::cloud()->assertExists($image_info['image_name']);
    }

    /**
     * @test
     */
    public function success_rekognition_get_NORMAL_level()
    {
        $this->client
            ->shouldReceive('detectModerationLabels')
            ->with(
                Mockery::any()
            )
            ->andReturn([
                'ModerationLabels' => [
                ],
                'ModerationModelVersion' => '<string>',
            ]);
        $this->request->image = UploadedFile::fake()->image('photo.png');
        $image_info = $this->image_shared_service->rekognition_forum_image($this->request);
        $this->assertNotEmpty($image_info['image_name']);
        $this->assertEquals(self::NORMAL, $image_info['level']);
        $this->assertEquals(0, $image_info['confidence']);
    }

    /**
     * @test
     */
    public function success_response_rekognition_get_NORMAL_level()
    {
        $this->client
            ->shouldReceive('detectModerationLabels')
            ->with(
                Mockery::any()
            )
            ->andReturn([
                'ModerationLabels' => [
                    'Confidence' => 90,
                ],
                'ModerationModelVersion' => '<string>',
            ]);
        $level = $this->image_shared_service->rekognition_response_image(UploadedFile::fake()->image('photo.png'));
        $this->assertEquals(self::NORMAL, $level);
    }

    /**
     * @test
     */
    public function success_response_rekognition_get_BLUR_level()
    {
        $this->client
            ->shouldReceive('detectModerationLabels')
            ->with(
                Mockery::any()
            )
            ->andReturn([
                'ModerationLabels' => [
                    [
                        'Confidence' => 90.1,
                    ],
                ],
                'ModerationModelVersion' => '<string>',
            ]);
        $level = $this->image_shared_service->rekognition_response_image(UploadedFile::fake()->image('photo.png'));
        $this->assertEquals(self::BlUR, $level);
    }
}

