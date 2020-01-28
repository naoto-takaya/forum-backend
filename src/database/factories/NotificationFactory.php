<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Infrastructure\Notification;
use App\Infrastructure\Response;
use App\User;
use Faker\Generator as Faker;

$factory->define(Notification::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class)->create()->id,
        'link' => 'forums/test/test_response_id',
        'content' => 'テスト用通知',
        'checked' => 0,
    ];
});
