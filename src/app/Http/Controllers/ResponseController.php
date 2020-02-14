<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResponseCreateRequest;
use App\Http\Requests\ResponseUpdateRequest;
use App\Services\ResponseService;

class ResponseController extends Controller
{
    protected $response_service;

    public function __construct(ResponseService $response_service)
    {
        $this->response_service = $response_service;
    }

    /**
     * レスポンスの作成
     * @param ResponseCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    protected function create(ResponseCreateRequest $request)
    {
        $this->response_service->create_response($request);
        return response()
            ->json([], 201);
    }

    /**
     * responseの更新
     */
    protected function update(ResponseUpdateRequest $request, $id)
    {
        $request->merge(['id' => $id]);
        $this->response_service->update_response($request);
        return response()
            ->json([], 204);
    }

    /**
     * responseの一覧取得
     */
    protected function list($forum_id)
    {
        $responses = $this->response_service->get_list($forum_id);
        return response()
            ->json(['responses' => $responses])
            ->setStatusCode(200);
    }

    /**
     * responseの取得
     */
    protected function get_response($id)
    {
        $response = $this->response_service->get($id);
        return response()
            ->json(['response' => $response])
            ->setStatusCode(200);
    }

    /**
     * リプライの取得
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    protected function get_replies($id)
    {
        $replies = $this->response_service->get_replies($id);
        return response()
            ->json(['replies' => $replies])
            ->setStatusCode(200);
    }

    /**
     * responseの削除
     */
    protected function remove($id)
    {
        $this->response_service->remove($id);
        return response()
            ->json([], 204);
    }
}
