<?php

namespace App\Providers;

use Aws\Comprehend\ComprehendClient;
use Illuminate\Support\ServiceProvider;

class ComprehendServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        if (strcmp(('APP_ENV'), 'production')) {
            $this->app->singleton(ComprehendClient::class, function ($app) {
                return new ComprehendClient([
                    'region' => 'us-east-1',
                    'version' => 'latest',
                ]);
            });
        } else {
            $this->app->singleton(ComprehendClient::class, function ($app) {
                return new ComprehendClient([
                    'region' => 'us-east-1',
                    'version' => 'latest',
                    'credentials' => [
                        'key' => env('AWS_ACCESS_KEY_ID'),
                        'secret' => env('AWS_SECRET_ACCESS_KEY'),
                    ],
                ]);
            });
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public
    function boot()
    {
        //
    }
}
