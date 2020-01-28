<?php

namespace App\Services;

use App\Http\Requests\ResponseCreateRequest;
use App\Http\Requests\ResponseUpdateRequest;
use App\Models\Response\ResponseInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResponseService
{
    private $response;
    private $comprehend;
    private $replies;

    public function __construct(ResponseInterface $response_interface, Comprehend $comprehend)
    {
        $this->response = $response_interface;
        $this->comprehend = $comprehend;
    }

    public function get($id)
    {
        $response = $this->response->get_response($id);
        return $response;
    }

    public function get_replies($id)
    {
        $this->replies = $this->response->get_replies($id);
        return $this->replies;
    }


    public function get_list()
    {
        $response = $this->response->get_response_list();
        return $response;
    }

    public function create(ResponseCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->image) {
                $filepath = Image::image_upload($request->image);
                $request = new Request($request->all());
                $request->merge(['image' => $filepath]);
            }

            // Comprehendによる感情分析値をセット
            $sentiment = $this->comprehend->get_sentiment($request->content);
            $request->merge(['sentiment' => $sentiment]);

            $request->merge(['user_id' => Auth::id()]);
            $this->response->create_response($request);

            // リプライの場合通知を作成する
            if ($request->response_id) {
                $this->response->create_notification_reply($request);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Image::image_delete($filepath);
            throw $e;
        }
    }

    public function update(ResponseUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->image) {
                $filepath = Image::image_upload($request->image);
                $request = new Request($request->all());
                $request->merge(['image' => $filepath]);
            }

            $this->response->update_response($request);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Image::image_delete($filepath);
            throw $e;
        }
    }

    public function remove($id)
    {
        $response = $this->response->remove_response($id);
        return $response;
    }
}
