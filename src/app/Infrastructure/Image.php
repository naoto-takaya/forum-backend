<?php

namespace App\Infrastructure;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use App\User;
use Vinkla\Hashids\Facades\Hashids;

class Image extends Model
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

    public function response()
    {
        return $this->belongsTo(Response::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param $response_id
     */
    public function create_response_image($response_id)
    {
        Image::fill([
            'name' => session()->get('image_name'),
            'confidence' => session()->get('confidence'),
            'response_id' => $response_id,
        ])->save();
        session()->forget('image_name');
        session()->forget('confidence');

    }

    /**
     * @param $forum_id
     */
    public function create_forum_image($forum_id)
    {
        Image::fill([
            'name' => session()->get('image_name'),
            'confidence' => session()->get('confidence'),
            'forum_id' => $forum_id
        ])->save();
        session()->forget('image_name');
        session()->forget('confidence');

    }

    /**
     * @param $forum_id
     * @return mixed
     */
    public function update_forum_image($forum_id)
    {
        $image = Image::where('forum_id', '=', $forum_id);
        $image->update([
            'name' => session()->get('image_name'),
            'confidence' => session()->get('confidence'),
        ]);

        session()->forget('image_name');
        session()->forget('confidence');
        return $image;
    }

    public function update_response_image($response_id)
    {
        $images = Image::where('response_id', '=', $response_id);
        $images->update([
            'name' => session()->get('image_name'),
            'confidence' => session()->get('confidence'),
        ]);
        session()->forget('image_name');
        session()->forget('confidence');
    }

    public function remove($id)
    {
    }

    /**
     * @param $forum_id
     * @return mixed
     */
    public function get_forum_image($forum_id)
    {
        return Image::where('forum_id', '=', $forum_id)->get();
    }

}
