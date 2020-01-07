<?php

namespace App\Infrastructure;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Forum;

class Response extends Model
{
    protected $guarded = [
        'id',
        'user_id',
        'forum_id',
        'created_date',
        'updated_date'
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    // public function user(){
    //     return $this->belongsTo(User::class);
    // }
}
