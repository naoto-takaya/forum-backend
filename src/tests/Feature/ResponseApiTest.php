<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Response;
use App\Infrastructure\Forum;
use App\User;
use Tests\TestCase;

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
    }

    /**
     * レスポンスの作成に成功する
     * @test
     */
    public function create_success()
    {

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
        $this->actingAs($this->user)->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);

        $response = Response::first();

        $result = $this->actingAs($this->user)->json('PATCH', route('responses.update'), [
            'id' => $response->id,
            'content' => "updated",
            'image' => $this->response->image,
        ]);
        $result->assertStatus(204);
        $updated_response = Response::first();
        $this->assertEquals("updated", $updated_response->content);
        $this->assertNotEquals($response->image, $updated_response->image);

        Storage::cloud()->assertExists($updated_response->filename);
    }

    /**
     * 更新に失敗する 更新対象のレコードが存在しない
     * @test
     */
    public function fail_update_not_exsit_record()
    {
        $this->actingAs($this->user)->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);

        $response = Response::first();
        $response->delete();

        $result = $this->actingAs($this->user)->json('PATCH', route('responses.update'), [
            'id' => $response->id,
            'content' => "updated",
            'image' => $this->response->image,
        ]);
        $result->assertStatus(404);
    }

    /**
     * 更新に失敗する 作成時と異なるユーザーが更新する
     * @test
     */
    public function fail_update_different_user()
    {
        $this->actingAs($this->user)->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);

        $response = Response::first();

        Auth::logout();
        $this->user = factory(User::class)->create();

        $result = $this->actingAs($this->user)->json('PATCH', route('responses.update'), [
            'id' => $response->id,
            'content' => "updated",
            'image' => $this->response->image,
        ]);

        $result->assertStatus(401);
        $this->assertEquals($this->response->content, $response->content);
    }

    /**
     * レスポンスを1件取得する
     * @test
     */
    public function get_response()
    {
        $this->actingAs($this->user)->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);
        $response = Response::first();
        $result = $this->json('GET', route('responses.get_response', ['response_id' => $response->id]));
        $result
            ->assertStatus(200)
            ->assertJson([
                'response' =>
                [
                    'id' => $response->id,
                    'content' => $response->content,
                    'image' => $response->image,
                ]
            ]);
    }

    /**
     * レスポンスを全件取得する
     * @test
     */
    public function get_response_list()
    {

        $result = $this->actingAs($this->user)->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);
        $result->assertStatus(201);

        $result = $this->json('GET', route('responses.list'));
        $result->assertStatus(200);
        $result->assertJsonFragment([
            'content' => $this->response->content,
        ]);
    }

    /**
     * レスポンスの削除に成功する
     * @test
     */
    public function success_delete_response()
    {
        $this->actingAs($this->user)->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);
        $response = Response::first();
        $result = $this->actingAs($this->user)->delete(route('responses.remove', ['response_id' => $response->id]));
        $result->assertStatus(204);
    }

    /**
     * レスポンスの削除に失敗する :削除対象のレコードが存在しない
     * @test
     */
    public function fail_delete_response_table_not_exist()
    {
        $this->actingAs($this->user)->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);
        $response = Response::first();
        Response::destroy($response->id);
        $result = $this->actingAs($this->user)->delete(route('responses.remove', ['response_id' => $response->id]));
        $result->assertStatus(404);
    }

    /**
     * レスポンスの削除に失敗する :権限がない
     * @test
     */
    public function fail_delete_response_require_auth()
    {
        $this->actingAs($this->user)->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);

        Auth::logout();

        $response = Response::first();
        $result = $this->delete(route('responses.remove', ['response_id' => $response->id]));
        $result->assertStatus(401);
    }
}
