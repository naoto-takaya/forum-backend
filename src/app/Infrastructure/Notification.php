<?php

namespace App\Infrastructure;

use App\Providers\NotificationRepositoryProvider;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Notification extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function response()
    {
        return $this->belongsTo(Response::class);
    }

    /**
     * @param $response_id
     */
    public function create_notification_reply($response_id)
    {
        $reply_count = Response::where('response_id', '=', $response_id)->count() + 1;
        $notification_user = Response::find($response_id)->user_id;
        $content = 'あなたの投稿に' . $reply_count . '件の返信がありました';
        // TODO: 通知から遷移先のリンクにアクセスできる
        $link = '';

        Notification::fill([
            'user_id' => $notification_user,
            'link' => $link,
            'content' => $content
        ])->save();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function get_notification_list()
    {
        return self::where('user_id', '=', Auth::id())->get();
    }

    /**
     *
     */
    public function notification_checked(){
        $notifications = self::where('user_id', '=', Auth::id());
        $notifications->update(array('checked' => 1));
    }
}
