<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Forum;
use App\Http\Requests\ForumRequest;
use App\Services\Image;

class ForumController extends Controller
{
    /**
     * forumの作成
     * @return \Illuminate\Http\Response
     */
    protected function create(ForumRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->image) {
                $filepath  = Image::image_upload($request->image);
            }
            $forum = Forum::create($request->all());
            DB::commit();
            return response()
                ->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Image::image_delete($filepath);
            return response()
                ->json([], 500);
        }
    }

    /**
     * forumの一覧取得
     */
    protected function list()
    {
        $forums = Forum::all();
        return response()
            ->json(['forums' => $forums])
            ->setStatusCode(200);
    }

    /**
     * forumの取得
     */
    protected function get_forum($id)
    {
        $forum = Forum::find($id);
        return response()
            ->json(['forum' => $forum])
            ->setStatusCode(200);
    }
}
