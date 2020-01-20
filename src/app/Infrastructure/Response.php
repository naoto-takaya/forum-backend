<?php

namespace App\Infrastructure;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Infrastructure\Forum;
use App\User;
use Vinkla\Hashids\Facades\Hashids;

class Response extends Model
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
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

    public function get($id)
    {
        return Response::find($id);
    }

    public function get_replies($id)
    {
        $response = $this->get($id);
        $replies = $response->replies()->orderBy('id')->get();
        return $replies;
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
