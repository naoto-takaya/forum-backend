<?php

namespace Tests\Feature;

use App\Infrastructure\Forum;
use App\Infrastructure\Image;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ForumApiTest extends TestCase
{
    private $user;
    private $forum;

    use RefreshDatabase;

    public function setup(): void
    {
        parent::setUp();
        Storage::fake('s3');
        $this->user = factory(User::class)->create();
        $this->forum = factory(Forum::class)->make(['user_id' => $this->user]);
    }

    /**
     * フォーラムの作成に成功する
     * @test
     */
    public function create_success()
    {

        $response = $this->actingAs($this->user)
            ->withSession(['image_name' => 'sample.png', 'confidence' => 1])
            ->json('POST', route('forums.create'), [
                'title' => $this->forum->title,
            ]);
        $response->assertStatus(201);
        $forum = Forum::first();
        $images = $forum->images()->get();

        $this->assertEquals($this->forum->title, $forum->title);
        $this->assertEquals($this->forum->user_id, $forum->user_id);

        foreach ($images as $image) {
            $this->assertEquals($forum->id, $image->forum_id);
            $this->assertEquals($image->name, 'sample.png');
            $this->assertEquals($image->confidence, 1);
        }
    }

    /**
     * 作成時、タイトルがない場合はバリデーションエラーを返す
     * @test
     */
    public function create_require_title()
    {
        $response = $this->actingAs($this->user)->json('POST', route('forums.create'));
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

        $response = $this->actingAs($this->user)
            ->withSession(['image_name' => 'updated.png', 'confidence' => 2])
            ->json('PATCH', route('forums.update', ['id' => $before_forum->id]), [
                'title' => $expected_forum->title,
            ]);

        $updated_forum = Forum::find($before_forum->id);
        $images = $updated_forum->images()->get();

        $response->assertStatus(204);
        $this->assertNotEquals($before_forum->title, $updated_forum->title);
        foreach ($images as $image) {
            $this->assertEquals($updated_forum->id, $image->forum_id);
            $this->assertEquals($image->name, 'updated.png');
            $this->assertEquals($image->confidence, 2);
        }
    }

    /**
     * 更新に失敗する 更新対象のレコードが存在しない
     * @test
     */
    public function fail_update_not_exist_record()
    {
        $before_forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        $expected_forum = factory(Forum::class)->make();

        Forum::destroy($before_forum->id);

        $response = $this->actingAs($this->user)->json('PATCH', route('forums.update', ['id' => $before_forum->id]), [
            'title' => $expected_forum->title,
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
        $forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        $forum->images = [factory(Image::class)->create(['forum_id' => $forum->id])->toArray()];

        $response = $this->json('GET', route('forums.get_forum', ['id' => $forum->id]));

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'forum' => $forum->toArray(),
            ]);
    }

    /**
     * フォーラムを全件取得する
     * @test
     */
    public function get_forum_list()
    {

        $forums = factory(Forum::class, 3)->create();

        $expected_json = $forums->each(function ($forum) {
            $forum->images = [factory(Image::class)->create(['forum_id' => $forum->id])->toArray()];
        });

        $response = $this->json('GET', route('forums.list'));
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'data' => $expected_json->toArray(),
            ]);
    }

    /**
     * ページネーションを使ってフォーラムの一覧の取得に成功する
     * @test
     */
    public function get_forum_list_pagination()
    {
        $paginate = 10;
        $forum_record_num = 30;
        $page_num = $forum_record_num / $paginate;
        $forums = factory(Forum::class, $forum_record_num)->create();
        $forums = $forums->each(function ($forum) {
            $forum->images = [];
        });

        for ($i = 1; $i < $page_num; $i++) {
            $expected_json = $forums->slice(($i - 1) * $paginate, $paginate)->values();

            $response = $this->json('GET', route('forums.list'), ['page' => $i]);
            $response
                ->assertStatus(200)
                ->assertJsonFragment([
                        'data' => $expected_json->toArray(),
                    ]
                );
        }
    }

    /**
     * フォーラムの削除に成功する
     * @test
     */
    public function success_delete_forum()
    {
        $forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        $image = factory(Image::class)->create(['forum_id' => $forum->id]);
        $response = $this->actingAs($this->user)->delete(route('forums.remove', ['id' => $forum->id]));


        $response
            ->assertStatus(204);
        $this->assertEmpty(Forum::find($forum->id));
        $this->assertEmpty(Image::find($image->id));

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
        $response->assertsertStatus(404);
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
