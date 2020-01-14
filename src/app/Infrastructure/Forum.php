<?php

namespace App\Infrastructure;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Response;
use App\User;
use Exception;
use Illuminate\Auth\AuthenticationException;

class Forum extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function response()
    {
        return $this->hasMany(Response::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function get($id)
    {
        return Forum::find($id);
    }

    public function create($request)
    {
        $request->merge(['user_id' => Auth::id()]);
        Forum::fill($request->all())->save();
        return true;
    }

    public function update($request = [], $options = [])
    {
        $forum = Forum::findOrFail($request->id);
        if ($forum->user->id != Auth::id()) {
            throw new AuthenticationException();
        }
        $forum->fill($request->all())->save();
        return true;
    }

    public function remove($id)
    {
        $forum = Forum::findOrFail($id);
        if ($forum->user->id != Auth::id()) {
            throw new AuthenticationException();
        }
        $forum->delete();
    }

    public function get_list()
    {
        return  Forum::all();
    }
}
