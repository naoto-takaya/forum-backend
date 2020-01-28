<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HealthCheckApiTest extends TestCase
{
    public function setup(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function get_status_code_200()
    {

        $response = $this->json('GET', route('health_check'));
        $response->assertStatus(200);

    }
}
