<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Infrastructure\Forum;
use Tests\TestCase;

class ForumApiTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setUp();
        Storage::fake('s3');
        $this->user = factory(User::class)->create();
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

        $before_forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        $expected_forum = factory(Forum::class)->make();

        $response = $this->actingAs($this->user)->json('PATCH', route('forums.update', ['id' => $before_forum->id]), [
            'title' => $expected_forum->title,
            'image' => $expected_forum->image,
        ]);

        $updated_forum = Forum::find($before_forum->id);

        $response->assertStatus(204);
        $this->assertNotEquals($before_forum->title, $updated_forum->title);
        $this->assertNotEquals($before_forum->image, $updated_forum->image);

        Storage::cloud()->assertExists($updated_forum->filename);
    }

    /**
     * 更新に失敗する 更新対象のレコードが存在しない
     * @test
     */
    public function fail_update_not_exsit_record()
    {
        $before_forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        $expected_forum = factory(Forum::class)->make();

        Forum::destroy($before_forum->id);

        $response = $this->actingAs($this->user)->json('PATCH', route('forums.update', ['id' => $before_forum->id]), [
            'title' => $expected_forum->title,
            'image' => $expected_forum->image,
        ]);

        $response->assertStatus(404);
    }

    /**
     * 更新時、認証されていない場合権限なしのエラーを返却
     * @test
     */
    public function fail_update_require_auth()
    {
        $before_forum = factory(Forum::class)->create();
        $expected_forum = factory(Forum::class)->make();

        $response = $this->json('PATCH', route('forums.update', ['id' => $before_forum->id]), [
            'title' => $expected_forum->title,
            'image' => $expected_forum->image,
        ]);

        $updated_forum = Forum::find($before_forum->id);

        $response->assertStatus(401);
        $this->assertEquals($before_forum->title, $updated_forum->title);
    }

    /**
     * 作成時と異なるユーザーが更新しようとした場合、認証エラーを返却
     * @test
     */
    public function fail_update_different_user()
    {
        $before_forum = factory(Forum::class)->create();
        $expected_forum = factory(Forum::class)->make();

        $response = $this->actingAs($this->user)->json('PATCH', route('forums.update', ['id' => $before_forum->id]), [
            'title' => $expected_forum->title,
            'image' => $expected_forum->image,
        ]);

        $updated_forum = Forum::find($before_forum->id);

        $response->assertStatus(401);
        $this->assertEquals($before_forum->title, $updated_forum->title);
    }

    /**
     * フォーラムを1件取得する
     * @test
     */
    public function get_forum()
    {
        $forum = factory(Forum::class)->create();
        $response = $this->json('GET', route('forums.get_forum', ['id' => $forum->id]));
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
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

        $forums = factory(Forum::class, 3)->states('get')->create();
        $response = $this->json('GET', route('forums.list'));
        $expected_json = $forums->map(function ($forum) {
            return [
                'id' => $forum->id,
                'user_id' => $forum->user_id,
                'title' => $forum->title,
                'image' => $forum->image,
                'created_at' => $forum->created_at->format('Y-m-d h:i:s'),
                'updated_at' => $forum->updated_at->format('Y-m-d H:i:s'),
            ];
        })->all();

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'forums' => $expected_json
            ]);
    }

    /**
     * フォーラムの削除に成功する
     * @test
     */
    public function success_delete_forum()
    {
        $forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user)->delete(route('forums.remove', ['id' => $forum->id]));
        $response->assertStatus(204);
    }

    /**
     * フォーラムの削除に失敗する :削除対象のレコードが存在しない
     * @test
     */
    public function fail_delete_forum_table_not_exist()
    {
        $forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        Forum::destroy($forum->id);
        $response = $this->actingAs($this->user)->delete(route('forums.remove', ['id' => $forum->id]));
        $response->assertStatus(404);
    }

    /**
     * フォーラムの削除に失敗する :作成時と異なるユーザーが削除する
     * @test
     */
    public function fail_delete_different_user()
    {
        $forum = factory(Forum::class)->create();

        $response = $this->actingAs($this->user)->delete(route('forums.remove', ['id' => $forum->id]));
        $response->assertStatus(401);
    }
}
