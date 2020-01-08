<?php

namespace App\Infrastructure;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Forum;

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

    // public function user(){
    //     return $this->belongsTo(User::class);
    // }

    public function get($id)
    {
        return Response::find($id);
    }

    public function create($request)
    {
        try {
            Response::fill($request->all())->save();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update($request = [], $options = [])
    {
        try {
            $response = Response::findOrFail($request->id);
            $response->fill($request->all())->save();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // public function delete($id)
    // {
    //     Response::destroy($id);
    // }

    public function get_list()
    {
        return  Response::all();
    }
}
