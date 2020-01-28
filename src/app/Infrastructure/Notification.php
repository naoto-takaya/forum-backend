<?php

namespace App\Infrastructure;

use App\Infrastructure\Response;
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
     * 通知情報の作成,DBに保存する
     * @param $request
     */
    public function create_notification_reply($request)
    {
        $reply_count = Response::where('response_id', '=', $request->response_id)->count();
        $notification_user = Response::find($request->response_id)->user_id;
        $content = 'あなたの投稿に' . $reply_count . '件の返信がありました';
        $link = 'forums/' . $request->forum_id . '/' . $request->response_id;

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
