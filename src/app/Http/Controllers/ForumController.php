<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForumRequest;
use App\Services\ForumService;

class ForumController extends Controller
{
    protected $forum_service;

    public function __construct(ForumService $forum_service)
    {
        $this->forum_service = $forum_service;
    }

    /**
     * @param ForumRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    protected function create_forum(ForumRequest $request)
    {
        $this->forum_service->create_forum($request);
        return response()
            ->json([], 201);
    }

    protected function update_forum(ForumRequest $request, $id)
    {
        $request->merge(['id' => $id]);
        $this->forum_service->update_forum($request);
        return response()
            ->json([], 204);
    }

    /**
     * forumの一覧取得
     */
    protected function get_forum_list()
    {
        $forums = $this->forum_service->get_forum_list();
        return response()
            ->json(['forums' => $forums])
            ->setStatusCode(200);
    }

    /**
     * forumの取得
     * @param $id
     * @return \Illuminate\Http\JsonResponse
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
    protected function remove($id)
    {
        $this->forum_service->remove($id);
        return response()
            ->json([], 204);
    }
}
