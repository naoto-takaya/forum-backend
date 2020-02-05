<?php

namespace Tests\Unit;

use App\Models\Image\ImageRepository;
use App\Infrastructure\Image;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\MockImageInterface;
use Aws\Rekognition\RekognitionClient;
use App\SharedServices\ImageSharedService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Faker\Generator as Faker;
use \Mockery;

class ImageUnitTest extends TestCase
{
    private $client;
    private $image_shared_service;

    public function setup(): void
    {
        parent::setUp();
        Storage::fake('s3');
        $this->client = Mockery::mock(RekognitionClient::class);
        $image_mock_repository = new MockImageRepository();
        $this->image_shared_service = new ImageSharedService($this->client, $image_mock_repository);
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

class MockImageRepository extends MockImageInterface
{
}
