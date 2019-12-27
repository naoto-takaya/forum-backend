<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Response;

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
}
