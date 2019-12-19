<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $guarded = [
        'id',
        'created_date',
        'updated_date'
    ];

    public function response()
    {
        return $this->hasMany('App\Response');
    }

    public function forum()
    {
        return $this->belongsTo('App\Forum');
    }
}
