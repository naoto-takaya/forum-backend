<?php

namespace App\Infrastructure;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Infrastructure\Forum;
use App\User;

class Response extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function get($id)
    {
        return Response::find($id);
    }

    public function create($request)
    {
        $request->merge(['user_id' => Auth::id()]);
        Response::fill($request->all())->save();
        return true;
    }

    public function update($request = [], $options = [])
    {
        $response = Response::findOrFail($request->id);
        if ($response->user->id != Auth::id()) {
            throw new AuthenticationException();
        }
        $response->fill($request->all())->save();
        return true;
    }

    public function remove($id)
    {
        $response = Response::findOrFail($id);
        if ($response->user->id != Auth::id()) {
            throw new AuthenticationException();
        }
        $response->delete();
        return true;
    }

    public function get_list()
    {
        return  Response::all();
    }
}
