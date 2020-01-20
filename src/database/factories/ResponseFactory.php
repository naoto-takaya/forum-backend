<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Infrastructure\Response;
use App\Infrastructure\Forum;
use App\User;
use Illuminate\Http\UploadedFile;
use Faker\Generator as Faker;

$factory->define(Response::class, function (Faker $faker) {
    return [
        'forum_id' => factory(Forum::class)->create()->id,
        'content' => $faker->sentence(),
        'user_id' => factory(User::class)->create()->id,
        'response_id' => null,
        'image' => UploadedFile::fake()->image('photo.jpg')
    ];
});

$factory->state(Response::class, 'get',  function (Faker $faker) {
    return [
        'image' => $faker->sentence()
    ];
});


$factory->state(Response::class, 'reply',  function (Faker $faker) {
    return [
        'image' => $faker->sentence(),
        'response_id' => factory(Response::class)->create()->id,
    ];
});

$factory->state(Response::class, 'reply_to_reply',  function (Faker $faker) {
    return [
        'image' => $faker->sentence(),
        'response_id' => factory(Response::class)->states('reply')->create()->id,
    ];
});
