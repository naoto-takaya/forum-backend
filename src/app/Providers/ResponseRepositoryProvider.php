<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Response\ResponseRepository;
use App\Models\Response\ResponseInterface;

class ResponseRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ResponseInterface::class, ResponseRepository::class);
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
