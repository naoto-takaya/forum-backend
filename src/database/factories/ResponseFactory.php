<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Infrastructure\Response;
use App\Infrastructure\Forum;
use App\User;
use Faker\Generator as Faker;

$factory->define(Response::class, function (Faker $faker) {
    return [
        'forum_id' => function () {
            return factory(Forum::class)->create()->id;
        },
        'content' => $faker->sentence(),
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'response_id' => null,
        'sentiment' => rand(1, 4),
        'is_deleted' => 0,
    ];
});

$factory->state(Response::class, 'forum_chooseable', function (Faker $faker) {
    return [
        'forum_id' => null,
    ];
});

$factory->state(Response::class, 'reply', function (Faker $faker) {
    return [
        'response_id' => factory(Response::class)->create()->id,
    ];
});

