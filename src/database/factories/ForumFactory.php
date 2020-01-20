<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Infrastructure\Forum;
use App\User;
use Illuminate\Http\UploadedFile;
use Faker\Generator as Faker;

$factory->define(Forum::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class)->create()->id,
        'title' => $faker->sentence(),
        'image' => UploadedFile::fake()->image('photo.jpg')
    ];
});

$factory->state(Forum::class, 'get',  function (Faker $faker) {
    return [
        'image' => $faker->sentence()
    ];
});
