<?php

use App\Infrastructure\Response;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResponseTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $response = new Response([
            'content' => '最初のレス',
        ]);
        $response->save();

        $response = new Response([
            'response_id' => 1,
            'content' => '1に対するリプライ',
        ]);
        $response->save();

        $response = new Response([
            'response_id' => 1,
            'content' => '1に対するリプライ id3より',
        ]);
        $response->save();

        $response = new Response([
            'response_id' => 1,
            'content' => '2に対するリプライ id4より',
        ]);
        $response->save();
    }
}
