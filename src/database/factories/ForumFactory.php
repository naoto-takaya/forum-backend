<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Infrastructure\Forum;
use Illuminate\Http\UploadedFile;
use Faker\Generator as Faker;

$factory->define(Forum::class, function (Faker $faker) {
    return [
        'user_id' => 1,
        'title' => $faker->sentence(),
        'image' => UploadedFile::fake()->image('photo.jpg')

    ];
});
