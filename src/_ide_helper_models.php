<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Infrastructure\Forum[] $forum
 * @property-read int|null $forum_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Infrastructure\Response[] $response
 * @property-read int|null $response_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Infrastructure{
/**
 * App\Infrastructure\Response
 *
 * @property int $id
 * @property int $user_id
 * @property int $forum_id
 * @property int|null $response_id
 * @property string $content
 * @property string|null $image
 * @property int|null $sentiment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Infrastructure\Forum $forum
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Infrastructure\Response[] $replies
 * @property-read int|null $replies_count
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response whereForumId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response whereResponseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response whereSentiment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Response whereUserId($value)
 */
	class Response extends \Eloquent {}
}

namespace App\Infrastructure{
/**
 * App\Infrastructure\Notification
 *
 * @property int $id
 * @property int $user_id
 * @property int $response_id
 * @property int $action
 * @property int $checked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Infrastructure\Response $response
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Notification whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Notification whereChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Notification whereResponseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Notification whereUserId($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Infrastructure{
/**
 * App\Infrastructure\Forum
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Infrastructure\Response[] $response
 * @property-read int|null $response_count
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Forum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Forum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Forum query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Forum whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Forum whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Forum whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Forum whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Forum whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Infrastructure\Forum whereUserId($value)
 */
	class Forum extends \Eloquent {}
}

