<?php

namespace App\Infrastructure;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Response;

class Forum extends Model
{
    protected $guarded = [
        'id',
        'user_id',
        'created_at',
        'updated_at'
    ];

    public function response()
    {
        return $this->hasMany(Response::class);
    }

    public function get($id)
    {
        return Forum::find($id);
    }

    public function create($request)
    {
        try {
            Forum::fill($request->all())->save();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update($request = [], $options = [])
    {
        try {
            $forum = Forum::findOrFail($request->id);
            $forum->fill($request->all())->save();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // public function delete($id)
    // {
    //     Forum::destroy($id);
    // }

    public function get_list()
    {
        return  Forum::all();
    }
}
