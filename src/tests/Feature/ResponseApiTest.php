<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Response;
use App\Infrastructure\Forum;
use Tests\TestCase;

class ResponseApiTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setUp();

        Storage::fake('s3');
        factory(Forum::class)->create();
        $this->forum = Forum::first();
        $this->response = factory(Response::class)->make();
    }

    /**
     * フォーラムの作成に成功する
     * @test
     */
    public function create_success()
    {

        $response = $this->json('POST', route('responses.create'), [
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
        $response = $this->json('POST', route('responses.create'), [
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
        $this->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);

        $response = Response::first();

        $result = $this->json('PATCH', route('responses.update'), [
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
        $this->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);

        $response = Response::first();
        $response->delete();

        $result = $this->json('PATCH', route('responses.update'), [
            'id' => $response->id,
            'content' => "updated",
            'image' => $this->response->image,
        ]);
        $result->assertStatus(404);
    }

    /**
     * フォーラムを1件取得する
     * @test
     */
    public function get_response()
    {
        $this->json('POST', route('responses.create'), [
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
     * フォーラムを全件取得する
     * @test
     */
    public function get_response_list()
    {

        $result = $this->json('POST', route('responses.create'), [
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
     * フォーラムの削除に成功する
     * @test
     */
    public function success_delete_response()
    {
        $this->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);
        $response = Response::first();
        $result = $this->delete(route('responses.delete', ['response_id' => $response->id]));
        $result->assertStatus(204);
    }

    /**
     * フォーラムの削除に失敗する :削除対象のレコードが存在しない
     * @test
     */
    public function fail_delete_response_table_not_exist()
    {
        $this->json('POST', route('responses.create'), [
            'forum_id' => $this->forum->id,
            'content' => $this->response->content,
            'image' => $this->response->image,
        ]);
        $response = Response::first();
        Response::destroy($response->id);
        $result = $this->delete(route('responses.delete', ['response_id' => $response->id]));
        $result->assertStatus(204);
    }
}
