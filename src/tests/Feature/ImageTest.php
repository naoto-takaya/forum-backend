<?php

namespace Tests\Feature;

use App\User;
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

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
        $this->user = factory(User::class)->create();
        $this->client = Mockery::mock(RekognitionClient::class);
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
                ],
                'ModerationModelVersion' => '<string>',
            ]);
        $this->app->instance('Aws\Rekognition\RekognitionClient', $this->client);
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
    public function success_rekognition_result_session_save()
    {
        $response = $this->actingAs($this->user)->json('POST', route('image.rekognition'), [
            'image' => UploadedFile::fake()->image('photo.png'),
        ]);

        $response->assertJsonFragment([
            'confidence' => 90,
        ]);

        $response->assertSessionHas('confidence', 90);
        $response->assertSessionHasAll(['image_name']);
        Storage::cloud()->assertExists(session()->get('image_name'));

    }

}
