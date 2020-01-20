<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->make();
    }
    /**
     * @test
     */
    public function success_register()
    {

        $response = $this->json('POST', route('register'), [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'password' => $this->user->password
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonFragment(['email' => $this->user->email]);
    }

    /**
     * 同じメールアドレスのユーザーは登録されない
     */
    public function failed_deplicate_email()
    {
        $user = factory(User::class)->create();

        $response = $this->json('POST', route('register'), [
            'name' => $this->user->name,
            'email' => $user->email,
            'password' => $this->user->password
        ]);

        $this->assertEmpty(User::all());
        $response
            ->assertStatus(422);
    }
}
