<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use App\Forum;
use Tests\TestCase;

class ForumApiTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setUp();

        Storage::fake('s3');
        $this->forum = factory(Forum::class)->make();
    }

    /**
     * フォーラムの作成に成功する
     * @test
     */
    public function create_success()
    {

        $response = $this->json('POST', route('forums.create'), [
            'title' => $this->forum->title,
            'image' => $this->forum->image,
        ]);
        $response->assertStatus(201);
        $forum = Forum::first();
        $this->assertEquals($this->forum->title, $forum->title);

        Storage::cloud()->assertExists($forum->filename);
    }

    /**
     * 作成時、タイトルがない場合はバリデーションエラーを返す
     * @test
     */
    public function create_require_title()
    {
        $response = $this->json('POST', route('forums.create'), [
            'image' => $this->forum->image,
        ]);
        $response->assertStatus(422);
        $this->assertEmpty(Forum::all());
    }

    /**
     * フォーラムを1件取得する
     * @test
     */
    public function get_forum()
    {
        $this->json('POST', route('forums.create'), [
            'title' => $this->forum->title,
            'image' => $this->forum->image,
        ]);
        $forum = Forum::first();
        $response = $this->json('GET', route('forums.get_forum', ['forum_id' => $forum->id]));
        $response
            ->assertStatus(200)
            ->assertJson([
                'forum' =>
                [
                    'id' => $forum->id,
                    'title' => $forum->title,
                    'image' => $forum->image,
                ]
            ]);
    }

    /**
     * フォーラムを全件取得する
     * @test
     */
    public function get_forum_list()
    {

        $forum = $this->json('POST', route('forums.create'), [
            'title' => $this->forum->title,
            'image' => $this->forum->image,
        ]);
        $forum->assertStatus(201);

        $response = $this->json('GET', route('forums.list'));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => $this->forum->title,
        ]);
    }
}
