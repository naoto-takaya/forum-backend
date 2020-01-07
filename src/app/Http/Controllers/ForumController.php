<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Infrastructure\Forum;
use App\Http\Requests\ForumRequest;
use App\Services\ForumService;
use App\Services\Image;

class ForumController extends Controller
{
    protected   $forum_service;

    public  function __construct(ForumService $forum_service)
    {
        $this->forum_service =  $forum_service;
    }
    /**
     * forumの作成
     * @return \Illuminate\Http\Response
     */
    protected function create(ForumRequest $request)
    {
        try {
            $this->forum_service->create($request);
            return response()
                ->json([], 201);
        } catch (\Exception $e) {
            return response()
                ->json([], 500);
        }
    }

    protected function update(ForumRequest $request)
    {
        try {
            $this->forum_service->update($request);
            return response()
                ->json([], 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()
                ->json([], 404);
        } catch (\Exception $e) {
            return response()
                ->json([], 500);
        }
    }

    /**
     * forumの一覧取得
     */
    protected function list()
    {
        $forums = $this->forum_service->get_list();
        return response()
            ->json(['forums' => $forums])
            ->setStatusCode(200);
    }

    /**
     * forumの取得
     */
    protected function get_forum($id)
    {
        $forum = $this->forum_service->get($id);
        return response()
            ->json(['forum' => $forum])
            ->setStatusCode(200);
    }

    /**
     * forumの削除
     */
    protected function delete($id)
    {
        try {
            $forum = $this->forum_service->delete($id);
            return response()
                ->json([], 204);
        } catch (\Exception $e) {
            return response()
                ->json([], 500);
        }
    }
}
