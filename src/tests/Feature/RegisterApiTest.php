<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->make();
        $this->data = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'password' => $this->user->password,
            'password_confirmation' => $this->user->password,
        ];
    }
    /**
     * @test
     */
    public function success_register()
    {

        $response = $this->json('POST', route('register'), $this->data);

        $user = User::first();
        $this->assertEquals($this->data['name'], $user->name);

        $response
            ->assertStatus(201)
            ->assertJson(['name' => $user->name]);
    }

    /**
     * パスワードと確認用パスワードが一致しない場合登録されない
     * @test
     */
    public function failed_password_wrong()
    {
        $this->data['password_confirmation'] = 'wrong_password';
        $response = $this->json('POST', route('register'), $this->data);
        $this->assertEmpty(User::all());
        $response
            ->assertStatus(422);
    }
}
