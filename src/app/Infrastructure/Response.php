<?php

namespace App\Infrastructure;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
        return Response::with(['images'])->find($id);
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function get_replies($id)
    {
        return Response::with(['images'])
            ->where('response_id', '=', $id)
            ->orderBy('created_at')
            ->get();
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
        return $response->delete();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|
     * \Illuminate\Database\Eloquent\Collection
     */
    public function get_response_list()
    {
        return Response::with(['images'])
            ->orderBy('created_at')
            ->get();
    }
}
