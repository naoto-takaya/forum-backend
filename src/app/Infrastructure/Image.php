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
            'level' => session()->get('level'),
            'response_id' => $response_id,
        ])->save();

        $this->remove_image_session();
    }

    /**
     * フォーラムの画像情報をDBに保存する
     * @param $request
     */
    public function create_forum_image($request)
    {
        Image::fill([
            'name' => $request->image_name,
            'confidence' => $request->confidence,
            'level' => $request->level,
            'forum_id' => $request->forum_id,
        ])->save();
    }

    /**
     * @param $request
     */
    public function update_forum_image($request)
    {
        Image::fill([
            'name' => $request->image_name,
            'confidence' => $request->confidence,
            'level' => $request->level,
            'forum_id' => $request->forum_id,
        ])->save();
    }

    /**
     * レスポンスの画像を更新する
     * @param $response_id
     */
    public function update_response_image($response_id)
    {
        $images = Image::where('response_id', '=', $response_id);
        $images->update([
            'name' => session()->get('image_name'),
            'confidence' => session()->get('confidence'),
            'level' => session()->get('level'),
        ]);
        $this->remove_image_session();
    }

    /**
     * 指定したIDの画像を削除する
     * @param $id
     */
    public function remove_image($id)
    {
        $image = Image::find($id);
        $image->delete();
    }

    /**
     *  セッションに保存された画像情報を削除する
     */
    public function remove_image_session()
    {
        session()->forget('image_name');
        session()->forget('confidence');
        session()->forget('level');
    }

    /**
     * レスポンスの画像を削除する
     * @param $response_id
     */
    public function delete_response_image($response_id)
    {
        $images = Image::where('response_id', '=', $response_id);
        $images->delete();
    }

    /**
     * @param $forum_id
     * @return mixed
     */
    public function get_forum_image($forum_id)
    {
        return Image::where('forum_id', '=', $forum_id)->get();
    }

    /**
     * @param $response_id
     * @return mixed
     */
    public function get_response_image($response_id)
    {
        return Image::where('response_id', '=', $response_id)->get();
    }
}
