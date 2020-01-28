<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notification\NotificationRepository;
use App\Models\Notification\NotificationInterface;

class NotificationRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(NotificationInterface::class, NotificationRepository::class);
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
