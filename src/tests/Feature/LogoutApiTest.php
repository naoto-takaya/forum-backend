<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogoutApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /**
     * ログアウトに成功する
     * @test
     */
    public function success_logout()
    {
        $response = $this->actingAs($this->user)
            ->json('GET', route('logout'));

        $response->assertStatus(200);
        $this->assertGuest();
    }
}
