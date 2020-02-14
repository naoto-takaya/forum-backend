<?php

namespace Tests\Unit;

use App\SharedServices\ImageSharedService;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ImageUnitTest extends TestCase
{
    private $client;
    private $image_shared_service;

    public function setup(): void
    {
        parent::setUp();
        Storage::fake('s3');
        $this->client = Mockery::mock(RekognitionClient::class);
        $this->image_shared_service = new ImageSharedService($this->client);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @test
     */
    public function success_rekognition_save()
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
                        'Name' => '<string>',
                        'ParentName' => '<string>',
                    ],
                    [
                        'Confidence' => 94.9,
                        'Name' => '<string>',
                        'ParentName' => '<string>',
                    ],
                ],
                'ModerationModelVersion' => '<string>',
            ]);

        $confidence = $this->image_shared_service->rekognition_save(UploadedFile::fake()->image('photo.png'));
        $this->assertEquals(94.9, $confidence);

    }

    public function success_rekognition_return_confidence()
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
        $confidence = $this->image_shared_service->rekognition_save(UploadedFile::fake()->image('photo.png'));
        $this->assertEquals(0, $confidence);
    }
}

