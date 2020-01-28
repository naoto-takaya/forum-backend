<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;
use App\Infrastructure\Notification;
use App\Infrastructure\Response;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $response_id;
    private $notifications;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->response_id = factory(Response::class)->create()->id;
    }

    /**
     * 通知情報の取得に成功する
     * @test
     */
    public function success_get_notifications()
    {
        $this->notifications = factory(Notification::class, 3)->create(['user_id' => $this->user->id]);
        $response = $this->actingAs($this->user)->json('GET', route('notifications.list'));
        $response->assertStatus(200);
    }

    /**
     * 同じレスポンスに対するリプライの通知件数が正しい
     * @test
     */
    public function confirm_notification()
    {
        // リプライの通知(既読)
        $notifications = factory(Notification::class, 3)->create(
            [
                'user_id' => $this->user->id,
            ]
        );

        $expected_json = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'user_id' => $notification->user_id,
                'link' => $notification->link,
                'content' => $notification->content,
                'checked' => $notification->checked,
                'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $notification->updated_at->format('Y-m-d H:i:s'),
            ];
        })->all();

        $response = $this->actingAs($this->user)->json('GET', route('notifications.list'));
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'notifications' =>
                    $expected_json,
            ]);
    }

    /**
     * 通知がない場合、空の配列が返却される
     * @test
     */
    public function get_nothing_collection_notifications()
    {

        $response = $this->actingAs($this->user)->json('GET', route('notifications.list'));

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'notifications' => []
            ]);
    }

    /**
     * 通知を既読状態にすることに成功する
     * @test
     */
    public function checked_notifications()
    {
        $notifications = factory(Notification::class, 3)->create(
            [
                'user_id' => $this->user->id,
            ]
        );
        $expected_json = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'user_id' => $notification->user_id,
                'link' => $notification->link,
                'content' => $notification->content,
                'checked' =>1,
                'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $notification->updated_at->format('Y-m-d H:i:s'),
            ];
        })->all();
        $this->actingAs($this->user)->json('PUT', route('notifications.checked'));
        $response = $this->actingAs($this->user)->json('GET', route('notifications.list'));
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'notifications' =>
                    $expected_json,
            ]);
    }
}
