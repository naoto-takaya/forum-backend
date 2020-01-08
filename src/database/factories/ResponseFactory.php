<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Infrastructure\Response;
use Illuminate\Http\UploadedFile;
use Faker\Generator as Faker;

$factory->define(Response::class, function (Faker $faker) {
    return [
        'content' => $faker->sentence(),
        'image' => UploadedFile::fake()->image('photo.jpg')
    ];
});
