<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $guarded = [
        'id',
        'topic_id',
        'created_date',
        'updated_date'
    ];

    public function topic()
    {
        return $this->belongsTo('App\Topic');
    }
}
