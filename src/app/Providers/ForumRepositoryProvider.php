<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Forum\ForumRepository;
use App\Models\Forum\ForumInterface;

class ForumRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ForumInterface::class, ForumRepository::class);
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
