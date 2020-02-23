<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Infrastructure\Image;
use Faker\Generator as Faker;

$factory->define(Image::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(),
        'forum_id' => null,
        'response_id' => null,
        'confidence' => $faker->randomFloat(2, 90, 100),
        'level' => rand(0, 2),
    ];
});

