<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Image\ImageRepository;
use App\Models\Image\ImageInterface;

class ImageRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ImageInterface::class, ImageRepository::class);
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
