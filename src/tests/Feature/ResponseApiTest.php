<?php

namespace Tests\Feature;

use App\Infrastructure\Forum;
use App\Infrastructure\Image;
use App\Infrastructure\Notification;
use App\Infrastructure\Response;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ResponseApiTest extends TestCase
{
    use RefreshDatabase;

    private $forum;
    private $user;
    private $response;
    private $image;
    private $mock_image;
    private $comprehend;

    public function setup(): void
    {
        parent::setUp();

        Storage::fake('s3');
        $this->forum = factory(Forum::class)->create();
        $this->user = factory(User::class)->create();
        $this->response = factory(Response::class)->make(['user_id' => $this->user->id]);
        $this->image = factory(Image::class)->make();
        $this->comprehend = Mockery::mock('App\Services\Comprehend');

        $this->comprehend
            ->shouldReceive('get_sentiment')
            ->with(Mockery::any())
            ->andReturn(rand(1, 4));
        $this->app->instance('App\Services\Comprehend', $this->comprehend);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }


    /**
     * レスポンスの作成に成功する
     * @test
     */
    public function create_success()
    {

        $mock_image = Mockery::mock('App\SharedServices\ImageSharedService');
        $mock_image
            ->shouldReceive('rekognition_response_image')
            ->with(Mockery::any())
            ->andReturn(
                ['image_name' => $this->image->name,
                    'confidence' => $this->image->confidence,
                    'level' => $this->image->level
                ]);
        $this->app->instance('App\SharedServices\ImageSharedService', $mock_image);


        $result = $this->actingAs($this->user)
            ->json('POST', route('responses.create'), [
                'forum_id' => $this->response->forum_id,
                'content' => $this->response->content,
                'image' => $this->image,
            ]);
        $response = Response::first();
        $images = $response->images()->get();

        $result->assertStatus(201);
        $this->assertEquals($this->response->content, $response->content);
        $this->assertEquals($this->response->forum_id, $response->forum_id);
        $this->assertEquals($this->response->user_id, $response->user_id);

        $this->assertNotEmpty($images);
        foreach ($images as $image) {
            $this->assertEquals($response->id, $image->response_id);
            $this->assertEquals($image->name, $this->image->name);
            $this->assertEquals($image->confidence, $this->image->confidence);
            $this->assertEquals($image->level, $this->image->level);
        }
    }

    /**
     * リプライの作成に成功し、通知が作成される
     * @test
     */
    public function create_reply_and_notification()
    {
        $response = factory(Response::class)->create();
        $result = $this->actingAs($this->user)
            ->withSession(['image_name' => 'sample.png', 'confidence' => 1])
            ->json('POST', route('responses.create'), [
                'forum_id' => $response->forum_id,
                'response_id' => $response->id,
                'content' => $this->response->content,
            ]);

        $notification = Notification::where('user_id', '=', $response->user_id)->first();
        $reply_response = Response::where('response_id', '=', $response->id)->first();

        $result->assertStatus(201);
        $this->assertEquals($response->id, $reply_response->response_id);
        $this->assertEquals($response->user_id, $notification->user_id);
        $this->assertEquals(false, $notification->checked);
    }

    /**
     * 作成時、内容がない場合はバリデーションエラーを返す
     * @test
     */
    public function create_require_content()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['image_name' => 'sample.png', 'confidence' => 1])
            ->json('POST', route('responses.create'), [
                'forum_id' => $this->forum->id,
            ]);
        $response->assertStatus(422);
        $this->assertEmpty(Response::all());
        $this->assertEmpty(Image::all());
    }

    /**
     * @test
     * responseの更新に成功する
     */
    public function success_update_response()
    {
        $this->mock_image = Mockery::mock('App\SharedServices\ImageSharedService');
        $this->mock_image
            ->shouldReceive('rekognition_response_image')
            ->with(Mockery::any())
            ->andReturn(
                ['image_name' => $this->image->name,
                    'confidence' => $this->image->confidence,
                    'level' => $this->image->level
                ]);
        $this->app->instance('App\SharedServices\ImageSharedService', $this->mock_image);


        $before_update_response = factory(Response::class)->create(['user_id'=>$this->user->id]);
        factory(Image::class)->create(['response_id' =>$before_update_response->id]);

        $expected_response = factory(Response::class)->make();


        $result = $this->actingAs($this->user)
            ->json('PATCH', route('responses.update', ['id' => $before_update_response->id]), [
                'content' => $expected_response->content,
                'image' => $this->image
            ]);


        $updated_response = Response::find($before_update_response->id);
        $updated_images = $updated_response->images()->get();

        $result->assertStatus(204);
        $this->assertEquals($expected_response->content, $updated_response->content);

        $this->assertNotEmpty($updated_images);
        foreach ($updated_images as $image) {
            $this->assertEquals($before_update_response->id, $image->response_id);
            $this->assertEquals($image->name, $this->image->name);
            $this->assertEquals($image->confidence, $this->image->confidence);
            $this->assertEquals($image->level, $this->image->level);
        }
    }

    /**
     * 画像添付したレスポンスを画像なしで更新した場合、画像が削除される
     * @test
     */
    public function succes_update_without_image(){

        $before_update_response = factory(Response::class)->create(['user_id'=>$this->user->id]);
        factory(Image::class)->create(['response_id' =>$before_update_response->id]);

        $expected_response = factory(Response::class)->make();


        $result = $this->actingAs($this->user)
            ->json('PATCH', route('responses.update', ['id' => $before_update_response->id]), [
                'content' => $expected_response->content,
            ]);

        $updated_response = Response::find($before_update_response->id);
        $images = $updated_response->images()->get();

        $result->assertStatus(204);
        $this->assertEquals($expected_response->content, $updated_response->content);
        $this->assertEmpty($images);

    }

    /**
     * 画像保存していないレスポンスを画像なしで更新した場合、画像なしで保存される
     * @test
     */
    public function succes_update_without_image_to_without_image(){

        $before_update_response = factory(Response::class)->create(['user_id'=>$this->user->id]);

        $expected_response = factory(Response::class)->make();


        $result = $this->actingAs($this->user)
            ->json('PATCH', route('responses.update', ['id' => $before_update_response->id]), [
                'content' => $expected_response->content,
            ]);

        $updated_response = Response::find($before_update_response->id);
        $images = $updated_response->images()->get();

        $result->assertStatus(204);
        $this->assertEquals($expected_response->content, $updated_response->content);
        $this->assertEmpty($images);

    }

    /**
     * 更新に失敗する 更新対象のレコードが存在しない
     * @test
     */
    public function fail_update_not_exist_record()
    {

        $response = factory(Response::class)->create();
        Response::destroy($response->id);

        $user = User::find($response->user_id);

        $result = $this->actingAs($user)->json('PATCH', route('responses.update', ['id' => $response->id]), [
            'content' => "updated",
            'image' => $response->image
        ]);
        $result->assertStatus(404);
    }

    /**
     * 更新に失敗する 作成時と異なるユーザーが更新する
     * @test
     */
    public function fail_update_different_user()
    {
        $response = factory(Response::class)->create();
        $user = factory(User::class)->create();

        $expected_response = factory(Response::class)->make();

        $result = $this->actingAs($user)->json('PATCH', route('responses.update', ['id' => $response->id]), [
            'content' => $expected_response->content,
            'image' => $expected_response->image,
        ]);
        $updated_response = Response::find($response->id);

        $result->assertStatus(401);
        $this->assertEquals($response->content, $updated_response->content);
    }

    /**
     * レスポンスを1件取得する
     * @test
     */
    public function get_response()
    {
        $response = factory(Response::class)->create(['user_id' => $this->user->id]);
        $images = factory(Image::class)->create(['response_id' => $response->id]);
        $response->images = [$images->toArray()];
        $response->user = $this->user->toArray();
        $response->replies_count = 0;

        $result = $this->json('GET', route('responses.get_response', ['id' => $response->id]));
        $result
            ->assertStatus(200)
            ->assertJsonFragment([
                'response' =>
                    $response->toArray()
            ]);
    }

    /**
     * レスポンスに対するリプライを取得する
     * @test
     */
    public function get_replies()
    {
        $response = factory(Response::class)->create(['user_id' => $this->user]);
        $replies = factory(Response::class, 3)->states('reply')->create(['response_id' => $response->id]);


        $expected_json = $replies->each(function ($reply) {
            $reply->images = [factory(Image::class)->create(['response_id' => $reply->id])->toArray()];
            $reply->replies_count = 0;
            $reply->user = User::find($reply->user_id)->toArray();
        });

        $result = $this->json('GET', route('responses.get_replies', ['id' => $response->id]));

        $result
            ->assertStatus(200)
            ->assertJsonCount(3, "replies")
            ->assertJsonFragment(["replies" => $expected_json->toArray()]);
    }

    /**
     * レスポンスを全件取得する
     * @test
     */
    public function get_response_list()
    {

        $forum = factory(Forum::class)->create();
        $responses = factory(Response::class, 3)->create(['forum_id' => $forum->id]);

        $expected_json = $responses->each(function ($response) {
            $response->images = [factory(Image::class)->create(['response_id' => $response->id])->toArray()];
            $response->replies_count = 0;
            $response->user = User::find($response->user_id)->toArray();
        });

        $result = $this->json('GET', route('responses.list', ['forum_id' => $forum->id]));

        $result->assertStatus(200)
            ->assertJsonCount(3, "responses")
            ->assertJsonFragment([
                'responses' => $expected_json->toArray(),
            ]);
    }

    /**
     * レスポンスの削除に成功する
     * 投稿内容、is_deletedが削除状態である
     * @test
     */
    public function success_delete_response()
    {
        $response = factory(Response::class)->create(['user_id' => $this->user->id]);
        factory(Image::class)->create(['response_id' => $response->id]);

        $result = $this->actingAs($this->user)->delete(route('responses.remove', ['id' => $response->id]));

        $deleted_response = Response::find($response->id);

        $result->assertStatus(204);
        $this->assertEquals('この投稿は削除されました', $deleted_response->content);
        $this->assertEquals(true, $deleted_response->is_deleted);
        $this->assertEmpty(Image::all());

    }

    /**
     * レスポンスの削除に失敗する :削除対象のレコードが存在しない
     * @test
     */
    public function fail_delete_response_table_not_exist()
    {
        $response = factory(Response::class)->create(['user_id' => $this->user->id]);
        $images = factory(Image::class)->create(['response_id' => $response->id]);

        Response::destroy($response->id);

        $result = $this->actingAs($this->user)->delete(route('responses.remove', ['id' => $response->id]));
        $result->assertStatus(404);
    }

    /**
     * レスポンスの削除に失敗する :権限がない
     * @test
     */
    public function fail_delete_response_require_auth()
    {
        $response = factory(Response::class)->create(['user_id' => $this->user->id]);
        $images = factory(Image::class)->create(['response_id' => $response->id]);

        $result = $this->delete(route('responses.remove', ['id' => $response->id]));

        $result->assertStatus(401);
        $this->assertNotEmpty(Response::find($response->id));
        $this->assertNotEmpty(Image::find($images->id));
    }
}
