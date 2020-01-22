<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Infrastructure\Response;
use App\Infrastructure\Forum;
use App\Services\Comprehend;
use App\User;
use Tests\TestCase;
use \Mockery;

class ResponseApiTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setUp();

        Storage::fake('s3');
        $this->forum = factory(Forum::class)->make();
        $this->user = factory(User::class)->create();
        $this->actingAs($this->user)->json('POST', route('forums.create'), [
            'title' => $this->forum->title,
            'image' => $this->forum->image,
        ]);
        $this->forum = Forum::first();
        $this->response = factory(Response::class)->make();
        $this->comprehend = Mockery::mock('App\Services\Comprehend');
    }

    /**
     * ComprehendのDIを行う
     */
    private function di_comprehend()
    {
        $this->comprehend
            ->shouldReceive('get_sentiment')
            ->with($this->response->content)
            ->andReturn(rand(1, 4));
        $this->app->instance('App\Services\Comprehend', $this->comprehend);
    }

    /**
     * レスポンスの作成に成功する
     * @test
     */
    public function create_success()
    {
        $this->di_comprehend();

        $response = $this->actingAs($this->user)->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);
        $response->assertStatus(201);
        $response = Response::first();
        $this->assertEquals($this->response->content, $response->content);

        Storage::cloud()->assertExists($response->filename);
    }

    /**
     * 作成時、内容がない場合はバリデーションエラーを返す
     * @test
     */
    public function create_require_content()
    {
        $this->di_comprehend();

        $response = $this->actingAs($this->user)->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'image' => $this->response->image,
        ]);
        $response->assertStatus(422);
        $this->assertEmpty(Response::all());
    }

    /**
     * @test
     * responseの更新に成功する
     */
    public function success_update_response()
    {
        $this->di_comprehend();

        $response = factory(Response::class)->create();
        $expected_response = factory(Response::class)->create();

        $user = User::find($response->user_id);

        $result = $this->actingAs($user)->json('PATCH', route('responses.update', ['id' => $response->id]), [
            'content' => $expected_response->content,
            'image' => $expected_response->image,
        ]);


        $updated_response = Response::find($response->id);

        $result->assertStatus(204);
        $this->assertEquals($expected_response->content, $updated_response->content);

        Storage::cloud()->assertExists($updated_response->image);
    }

    /**
     * 更新に失敗する 更新対象のレコードが存在しない
     * @test
     */
    public function fail_update_not_exsit_record()
    {
        $this->di_comprehend();

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
        $this->di_comprehend();

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
        $response = factory(Response::class)->create();
        $result = $this->json('GET', route('responses.get_response', ['id' => $response->id]));
        $result
            ->assertStatus(200)
            ->assertJsonFragment([
                'response' =>
                [
                    $response
                ]
            ]);
    }

    /**
     * レスポンスに対するリプライを取得する
     * @test
     */
    public function get_replies()
    {
        $response = factory(Response::class)->states('get')->create();
        $replies = factory(Response::class, 3)->states('reply')->create(['response_id' => $response->id]);

        $result = $this->json('GET', route('responses.get_replies', ['id' => $response->id]));

        $expected_json = $replies->map(function ($reply) {
            return [
                'id' => $reply->id,
                'user_id' => $reply->user_id,
                'forum_id' => $reply->forum_id,
                'content' => $reply->content,
                'image' => $reply->image,
                'sentiment' => $reply->sentiment,
                'response_id' => $reply->response_id,
                'created_at' => $reply->created_at->format('Y-m-d h:i:s'),
                'updated_at' => $reply->updated_at->format('Y-m-d H:i:s'),
            ];
        })->all();

        $result
            ->assertStatus(200)
            ->assertJsonCount(3, "replies")
            ->assertJsonFragment(["replies" => $expected_json]);
    }

    /**
     * レスポンスを全件取得する
     * @test
     */
    public function get_response_list()
    {

        $responses = factory(Response::class, 3)->states('get')->create();

        $result = $this->json('GET', route('responses.list'));
        $expected_json = $responses->map(function ($response) {
            return [
                'id' => $response->id,
                'user_id' => $response->user_id,
                'forum_id' => $response->forum_id,
                'content' => $response->content,
                'image' => $response->image,
                'sentiment' => $response->sentiment,
                'response_id' => $response->response_id,
                'created_at' => $response->created_at->format('Y-m-d h:i:s'),
                'updated_at' => $response->updated_at->format('Y-m-d H:i:s'),
            ];
        })->all();

        $result->assertStatus(200)
            ->assertJsonCount(3, "responses")
            ->assertJsonFragment([
                'responses' => $expected_json,
            ]);
    }

    /**
     * レスポンスの削除に成功する
     * @test
     */
    public function success_delete_response()
    {
        $response = factory(Response::class)->states('get')->create();
        $user = User::find($response->user_id);
        $result = $this->actingAs($user)->delete(route('responses.remove', ['id' => $response->id]));
        $result->assertStatus(204);
    }

    /**
     * レスポンスの削除に失敗する :削除対象のレコードが存在しない
     * @test
     */
    public function fail_delete_response_table_not_exist()
    {
        $response = factory(Response::class)->states('get')->create();
        $user = User::find($response->user_id);

        Response::destroy($response->id);

        $result = $this->actingAs($user)->delete(route('responses.remove', ['id' => $response->id]));
        $result->assertStatus(404);
    }

    /**
     * レスポンスの削除に失敗する :権限がない
     * @test
     */
    public function fail_delete_response_require_auth()
    {
        $response = factory(Response::class)->states('get')->create();
        $result = $this->delete(route('responses.remove', ['id' => $response->id]));
        $result->assertStatus(401);
    }
}
