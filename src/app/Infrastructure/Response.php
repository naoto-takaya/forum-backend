<?php

namespace App\Infrastructure;

use App\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class Response extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'is_deleted'
    ];

    public function getRouteKey(): string
    {
        return Hashids::connection('response')->encode($this->getKey());
    }

    public function resolveRouteBinding($value): ?Model
    {
        $value = Hashids::connection('response')->decode($value)[0] ?? null;
        return $this->where($this->getRouteKeyName(), $value)->first();
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(Response::class, 'response_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|
     * \Illuminate\Database\Eloquent\Builder[]|
     * \Illuminate\Database\Eloquent\Collection|
     * Model|
     * null
     */
    public function get_response($id)
    {
        $response = Response::with(['images'])
            ->with(['user'])
            ->find($id);
        $response->replies_count = $response->replies()->count();

        return $response;
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function get_replies($id)
    {
        $replies = Response::with(['images'])
            ->with(['user'])
            ->where('response_id', '=', $id)
            ->orderBy('created_at')
            ->get();

        foreach ($replies as $reply) {
            $reply->replies_count = $reply->replies()->count();
        }
        return $replies;
    }


    /**
     * @param $request
     * @return Response|Model
     */
    public function create_response($request)
    {
        return Response::create($request->all());
    }

    /**
     * @param $request
     * @return Response|Response[]|\Illuminate\Database\Eloquent\Collection|Model
     * @throws AuthenticationException
     */
    public function update_response($request)
    {
        $response = Response::findOrFail($request->id);
        if ($response->user->id != Auth::id()) {
            throw new AuthenticationException();
        }

        $response->fill($request->all())->save();
        return $response;
    }

    /**
     * 投稿を削除状態にする
     * @param $id
     * @return bool|null
     * @throws AuthenticationException
     */
    public function remove_response($id)
    {
        $response = Response::findOrFail($id);
        if ($response->user->id != Auth::id()) {
            throw new AuthenticationException();
        }
        $response->is_deleted = true;
        $response->content = 'この投稿は削除されました';
        return $response->save();
    }

    /**
     * @param $forum_id
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function get_response_list($forum_id)
    {
        $responses = Response::with(['images'])
            ->with(['user'])
            ->where('forum_id', '=', $forum_id)
            ->whereNull('response_id')
            ->orderBy('created_at')
            ->get();

        // レスポンスに対する返信の数を取得
        foreach ($responses as $response) {
            $response->replies_count = $response->replies()->count();
        }

        return $responses;
    }

}
