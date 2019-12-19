<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\Image;
use Aws\Rekognition\RekognitionClient;
use App\Forum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
        $this->forum = factory(Forum::class)->make();
    }

    /**
     * 画像アップロードに成功する
     * @test
     */
    public function success_upload_image_file()
    {
        $filepath = Image::image_upload($this->forum->image);
        Storage::cloud()->assertExists($filepath);
    }

    /**
     * 画像削除に成功する
     * @test
     */
    public function success_delete_image_file()
    {
        $filepath = Image::image_upload($this->forum->image);
        Image::image_delete($filepath);
        Storage::cloud()->assertMissing($filepath);
    }

    /**
     * Rekognitionに画像節度を診断させ、Confidenceが返却される
     * @test
     */
    public function rekognition()
    {
        $contents = $this->forum->image;
        $factoryMock = \Mockery::mock('overload:' . RekognitionClient::class);
        $factoryMock->shouldReceive('detectModerationLabels')
            ->once()
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
        $result = Image::rekognition_image($contents);
        $column = array_column($result['ModerationLabels'], 'Confidence');
        $this->assertEquals([90, 94.9], $column);
    }
}
