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
        $this->response_service =  $response_service;
    }

    /**
     * responseの作成
     * @return \Illuminate\Http\Response
     */
    protected function create(ResponseCreateRequest $request)
    {
        try {
            $this->response_service->create($request);
            return response()
                ->json([], 201);
        } catch (\Exception $e) {
            return response()
                ->json([], 500);
        }
    }

    /**
     * responseの更新
     */
    protected function update(ResponseUpdateRequest $request)
    {
        try {
            $this->response_service->update($request);
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
     * responseの一覧取得
     */
    protected function list()
    {
        $responses = $this->response_service->get_list();
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
     * responseの削除
     */
    protected function delete($id)
    {
        try {
            $response = $this->response_service->delete($id);
            return response()
                ->json([], 204);
        } catch (\Exception $e) {
            return response()
                ->json([], 500);
        }
    }
}
