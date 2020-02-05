<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Infrastructure\Forum;
use App\Infrastructure\Image;
use App\User;
use Illuminate\Http\UploadedFile;
use Faker\Generator as Faker;

$factory->define(Forum::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class)->create()->id,
        'title' => $faker->sentence(),
    ];
});

