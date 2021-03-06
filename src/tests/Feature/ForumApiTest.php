<?php

namespace Tests\Feature;

use App\Infrastructure\Forum;
use App\Infrastructure\Image;
use App\Infrastructure\Response;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ForumApiTest extends TestCase
{
    private $user;
    private $forum;
    private $image;
    private $mock_image;

    use RefreshDatabase;

    public function setup(): void
    {
        parent::setUp();
        Storage::fake('s3');
        $this->user = factory(User::class)->create();
        $this->forum = factory(Forum::class)->make(['user_id' => $this->user]);
        $this->image = factory(Image::class)->make();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * フォーラムの作成に成功する
     * @test
     */
    public function create_success()
    {
        $this->mock_image = Mockery::mock('App\SharedServices\ImageSharedService');
        $this->mock_image
            ->shouldReceive('rekognition_forum_image')
            ->with(Mockery::any())
            ->andReturn(
                ['image_name' => $this->image->name,
                    'confidence' => $this->image->confidence,
                    'level' => $this->image->level
                ]);
        $this->app->instance('App\SharedServices\ImageSharedService', $this->mock_image);

        $response = $this->actingAs($this->user)
            ->json('POST', route('forums.create'), [
                'title' => $this->forum->title,
                'image' => UploadedFile::fake()->create('photo.png'),
            ]);
        $response->assertStatus(201);
        $forum = Forum::first();
        $images = $forum->images()->get();

        $this->assertEquals($this->forum->title, $forum->title);
        $this->assertEquals($this->forum->user_id, $forum->user_id);

        foreach ($images as $image) {
            $this->assertEquals($forum->id, $image->forum_id);
            $this->assertEquals($image->name, $this->image->name);
            $this->assertEquals($image->confidence, $this->image->confidence);
            $this->assertEquals($image->level, $this->image->level);
        }
    }

    /**
     * フォーラムに添付した画像がRekognitionによって投稿不可となった場合、DBに保存されない
     * @test
     */
    public function create_success_no_image()
    {

        $this->mock_image = Mockery::mock('App\SharedServices\ImageSharedService');
        $this->mock_image
            ->shouldReceive('rekognition_forum_image')
            ->with(Mockery::any())
            ->andReturn(
                ['image_name' => $this->image->name,
                    'confidence' => 95,
                    'level' => 0,
                ]);
        $this->app->instance('App\SharedServices\ImageSharedService', $this->mock_image);

        $response = $this->actingAs($this->user)
            ->json('POST', route('forums.create'), [
                'title' => $this->forum->title,
                'image' => UploadedFile::fake()->create('photo.png'),
            ]);

        $forum = Forum::first();
        $images = $forum->images()->get();

        $response->assertStatus(201);
        $this->assertEmpty($images);

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
        $this->mock_image = Mockery::mock('App\SharedServices\ImageSharedService');
        $this->mock_image
            ->shouldReceive('rekognition_forum_image')
            ->with(Mockery::any())
            ->andReturn(
                ['image_name' => $this->image->name,
                    'confidence' => $this->image->confidence,
                    'level' => $this->image->level
                ]);
        $this->app->instance('App\SharedServices\ImageSharedService', $this->mock_image);

        $before_forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        $expected_forum = factory(Forum::class)->make();

        $response = $this->actingAs($this->user)
            ->json('PATCH', route('forums.update', ['id' => $before_forum->id]), [
                'title' => $expected_forum->title,
                'image' => UploadedFile::fake()->create('photo.png'),
            ]);

        $updated_forum = Forum::find($before_forum->id);
        $images = $updated_forum->images()->get();

        $response->assertStatus(204);
        $this->assertNotEquals($before_forum->title, $updated_forum->title);
        foreach ($images as $image) {
            $this->assertEquals($image->name, $this->image->name);
            $this->assertEquals($image->confidence, $this->image->confidence);
            $this->assertEquals($image->level, $this->image->level);
        }
    }

    /**
     * フォーラム更新時、imageがnullの場合画像が削除される
     * @test
     */
    public function success_update_without_image()
    {
        $before_forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        factory(Image::class)->create(['forum_id' => $before_forum->id]);
        $expected_forum = factory(Forum::class)->make();

        $response = $this->actingAs($this->user)
            ->json('PATCH', route('forums.update', ['id' => $before_forum->id]), [
                'title' => $expected_forum->title,
            ]);

        $updated_forum = Forum::find($before_forum->id);
        $images = $updated_forum->images()->get();
        $this->assertEmpty($images);

        $response->assertStatus(204);
    }

    /**
     * フォーラム更新時、更新前からimageがない場合もimageがnullの場合画像は存在しない
     * @test
     */
    public function success_update_without_image_to_without_image()
    {
        $before_forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        $expected_forum = factory(Forum::class)->make();

        $response = $this->actingAs($this->user)
            ->json('PATCH', route('forums.update', ['id' => $before_forum->id]), [
                'title' => $expected_forum->title,
            ]);

        $updated_forum = Forum::find($before_forum->id);
        $images = $updated_forum->images()->get();
        $this->assertEmpty($images);

        $response->assertStatus(204);
    }

    /**
     * フォーラム作成時、添付画像が査定によりアップロード不可の場合、画像は保存されない
     * @test
     */
    public function success_update_rekognition_get_level_BAN()
    {
        $this->mock_image = Mockery::mock('App\SharedServices\ImageSharedService');
        $this->mock_image
            ->shouldReceive('rekognition_forum_image')
            ->with(Mockery::any())
            ->andReturn(
                ['image_name' => $this->image->name,
                    'confidence' => 95,
                    'level' => 0,
                ]);
        $this->app->instance('App\SharedServices\ImageSharedService', $this->mock_image);

        $before_forum = factory(Forum::class)->create(['user_id' => $this->user->id]);
        $expected_forum = factory(Forum::class)->make();

        $response = $this->actingAs($this->user)
            ->json('PATCH', route('forums.update', ['id' => $before_forum->id]), [
                'title' => $expected_forum->title,
                'image' => UploadedFile::fake()->create('photo.png'),
            ]);

        $updated_forum = Forum::find($before_forum->id);
        $images = $updated_forum->images()->get();

        $response->assertStatus(204);
        $this->assertEmpty($images);

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
            $forum->user = User::find($forum->user_id)->toArray();
            $forum->responses_count = 0;
        });

        $response = $this->json('GET', route('forums.list'));

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'data' => $expected_json->toArray(),
            ]);
    }

    /**
     * フォーム一覧取得時、レスポンスのカウントを取得している
     * @test
     */
    public function get_forum_list_with_responses_count()
    {
        $forum = factory(Forum::class)->create();
        factory(Response::class, 5)->create(['forum_id' => $forum->id]);

        $forum->images = [factory(Image::class)->create(['forum_id' => $forum->id])->toArray()];
        $forum->user = User::find($forum->user_id)->toArray();
        $forum->responses_count = 5;

        $response = $this->json('GET', route('forums.list'));

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'data' => [$forum->toArray()],
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
            $forum->user = User::find($forum->user_id)->toArray();
            $forum->responses_count = 0;
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
