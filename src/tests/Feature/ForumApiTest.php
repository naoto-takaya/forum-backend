<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use App\Infrastructure\Forum;
use App\User;
use Tests\TestCase;

class ForumApiTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        Storage::fake('s3');
        $this->forum = factory(Forum::class)->make();
    }

    /**
     * フォーラムの作成に成功する
     * @test
     */
    public function create_success()
    {

        $response = $this->actingAs($this->user)->json('POST', route('forums.create'), [
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
        $response = $this->actingAs($this->user)->json('POST', route('forums.create'), [
            'image' => $this->forum->image,
        ]);
        $response->assertStatus(422);
        $this->assertEmpty(Forum::all());
    }

    /**
     * 作成時、認証されていない場合権限なしのエラーを返却
     * @test
     */
    public function create_require_auth()
    {
        $response = $this->json('POST', route('forums.create'), [
            'title' => $this->forum->title,
            'image' => $this->forum->image,
        ]);

        $response->assertStatus(401);
        $this->assertEmpty(Forum::all());
    }

    /**
     * @test
     * forumの更新に成功する
     */
    public function success_update_forum()
    {
        $response = $this->actingAs($this->user)->json('POST', route('forums.create'), [
            'title' => $this->forum->title,
            'image' => $this->forum->image,
        ]);

        $forum = Forum::first();

        $response = $this->actingAs($this->user)->json('PATCH', route('forums.update'), [
            'id' => $forum->id,
            'title' => "updated",
            'image' => $this->forum->image,
        ]);
        $response->assertStatus(204);
        $updated_forum = Forum::first();
        $this->assertEquals("updated", $updated_forum->title);
        $this->assertNotEquals($forum->image, $updated_forum->image);

        Storage::cloud()->assertExists($updated_forum->filename);
    }

    /**
     * 更新に失敗する 更新対象のレコードが存在しない
     * @test
     */
    public function fail_update_not_exsit_record()
    {
        $response = $this->actingAs($this->user)->json('POST', route('forums.create'), [
            'title' => $this->forum->title,
            'image' => $this->forum->image,
        ]);

        $forum = Forum::first();
        $forum->delete();

        $response = $this->actingAs($this->user)->json('PATCH', route('forums.update'), [
            'id' => $forum->id,
            'title' => "updated",
            'image' => $this->forum->image,
        ]);
        $response->assertStatus(404);
    }

    /**
     * 更新時、認証されていない場合権限なしのエラーを返却
     * @test
     */
    public function fail_update_require_auth()
    {
        $this->actingAs($this->user)->json('POST', route('forums.create'), [
            'title' => $this->forum->title,
            'image' => $this->forum->image,
        ]);

        $forum = Forum::first();

        $response = $this->json('PATCH', route('forums.update'), [
            'id' => $forum->id,
            'title' => "updated",
            'image' => $this->forum->image,
        ]);

        $response->assertStatus(401);
        $this->assertEquals($this->forum->title, $forum->title);
    }

    /**
     * フォーラムを1件取得する
     * @test
     */
    public function get_forum()
    {
        $this->actingAs($this->user)->json('POST', route('forums.create'), [
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

        $forum = $this->actingAs($this->user)->json('POST', route('forums.create'), [
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

    /**
     * フォーラムの削除に成功する
     * @test
     */
    public function success_delete_forum()
    {
        $forum = $this->actingAs($this->user)->json('POST', route('forums.create'), [
            'title' => $this->forum->title,
            'image' => $this->forum->image,
        ]);
        $forum = Forum::first();
        $response = $this->actingAs($this->user)->delete(route('forums.delete', ['forum_id' => $forum->id]));
        $response->assertStatus(204);
    }

    /**
     * フォーラムの削除に失敗する :削除対象のレコードが存在しない
     * @test
     */
    public function fail_delete_forum_table_not_exist()
    {
        $forum = $this->actingAs($this->user)->actingAs($this->user)->json('POST', route('forums.create'), [
            'title' => $this->forum->title,
            'image' => $this->forum->image,
        ]);
        $forum = Forum::first();
        Forum::destroy($forum->id);
        $response = $this->delete(route('forums.delete', ['forum_id' => $forum->id]));
        $response->assertStatus(204);
    }
}
