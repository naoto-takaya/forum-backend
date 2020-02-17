<?php

namespace App\Infrastructure;

use App\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class Forum extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function getRouteKey(): string
    {
        return Hashids::connection('forum')->encode($this->getKey());
    }

    public function resolveRouteBinding($value): ?Model
    {
        $value = Hashids::connection('forum')->decode($value)[0] ?? null;

        return $this->where($this->getRouteKeyName(), $value)->first();
    }

    public function response()
    {
        return $this->hasMany(Response::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function get($id)
    {
        return Forum::with(['images'])->find($id);
    }

    /**
     * @param $request
     * @return Forum|Model
     */
    public function create_forum($request)
    {
        $request->merge(['user_id' => Auth::id()]);
        return Forum::create($request->all());
    }

    /**
     * @param $request
     * @return Forum|Forum[]|\Illuminate\Database\Eloquent\Collection|Model
     * @throws AuthenticationException
     */
    public function update_forum($request)
    {
        $forum = Forum::findOrFail($request->id);
        if ($forum->user->id != Auth::id()) {
            throw new AuthenticationException();
        }
        $forum->fill($request->all())->save();
        return $forum;
    }

    public function remove($id)
    {
        $forum = Forum::findOrFail($id);
        if ($forum->user->id != Auth::id()) {
            throw new AuthenticationException();
        }
        $forum->delete();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function get_forum_list()
    {
        return Forum::with('images')->simplePaginate(10);
    }
}
