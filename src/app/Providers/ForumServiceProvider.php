<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ForumService;

class ForumServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ForumService::class, ForumService::class);
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
