<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Comprehend;
use Aws\Comprehend\ComprehendClient;

class ComprehendServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton(ComprehendClient::class, function ($app) {
            return   new ComprehendClient([
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
