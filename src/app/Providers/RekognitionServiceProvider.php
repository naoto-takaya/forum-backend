<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Aws\Rekognition\RekognitionClient;

class ComprehendServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RekognitionClient::class, function ($app) {
            return new RekognitionClient([
                'region' => 'us-east-1',
                'version' => 'latest',
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
            ]);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
