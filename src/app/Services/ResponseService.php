<?php

namespace App\Services;

use App\Http\Requests\ResponseCreateRequest;
use App\Http\Requests\ResponseUpdateRequest;
use App\Models\Response\ResponseInterface;
use App\SharedServices\ImageSharedService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResponseService
{
    private $response;
    private $comprehend;
    private $image;

    /**
     * ResponseService constructor.
     * @param ResponseInterface $response_interface
     * @param Comprehend $comprehend
     * @param ImageSharedService $image_shared_service
     */
    public function __construct(ResponseInterface $response_interface, Comprehend $comprehend, ImageSharedService $image_shared_service)
    {
        $this->response = $response_interface;
        $this->comprehend = $comprehend;
        $this->image = $image_shared_service;
    }

    /**
     * 指定したIDのレスポンスを取得する
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->response->get_response($id);

    }

    /**
     * 指定したIDにリプライしたレスポンス一覧を取得する
     * @param $id
     * @return mixed
     */
    public function get_replies($id)
    {
        return $this->response->get_replies($id);
    }

    /**
     * レスポンス一覧を取得する
     * @param $forum_id
     * @return mixed
     */
    public function get_list($forum_id)
    {
        return $this->response->get_response_list($forum_id);
    }

    /**
     * レスポンスの作成
     * @param ResponseCreateRequest $request
     * @throws \Exception
     */
    public function create_response(ResponseCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            // Comprehendによる感情分析値をセット
            $sentiment = $this->comprehend->get_sentiment($request->content);
            $request->merge(['sentiment' => $sentiment]);

            $request->merge(['user_id' => Auth::id()]);
            $response = $this->response->create_response($request);

            // 画像がアップロードされている場合、DBに保存する
            if ($request->image) {
                $request->merge(['id' => $response->id]);
                $image_info = $this->image->rekognition_response_image($request);
                $request->merge($image_info);
                $this->response->create_image($request);
            }

            // リプライの場合通知を作成する
            if ($response->response_id) {
                $this->response->create_notification_reply($response->response_id);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * レスポンスの更新
     * @param ResponseUpdateRequest $request
     * @throws \Exception
     */
    public function update_response(ResponseUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            // Comprehendによる感情分析値をセット
            $sentiment = $this->comprehend->get_sentiment($request->content);
            $request->merge(['sentiment' => $sentiment]);

            $response = $this->response->update_response($request);

            // 画像がアップロードされている場合、DBに保存する
            if ($request->image) {
                $request->merge(['id' => $response->id]);
                $image_info = $this->image->rekognition_response_image($request);
                $request->merge($image_info);
                $this->response->update_image($request);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * レスポンスの削除
     * @param $id
     * @throws \Exception
     */
    public function remove($id)
    {
        DB::beginTransaction();
        try {
            $this->response->remove_response($id);
            $this->response->delete_image($id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
