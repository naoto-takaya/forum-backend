<?php

namespace App\Services;

use App\Http\Requests\ResponseCreateRequest;
use App\Http\Requests\ResponseUpdateRequest;
use App\Models\Response\ResponseInterface;
use App\Services\Comprehend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResponseService
{
    private $response;
    private $comprehend;

    public function __construct(ResponseInterface $response_interface, Comprehend $comprehend)
    {
        $this->response = $response_interface;
        $this->comprehend = $comprehend;
    }

    public function get($id)
    {
        $response = $this->response->get($id);
        return $response;
    }

    public function get_replies($id)
    {
        return  $this->response->get_replies($id);
    }


    public function get_list()
    {
        $response = $this->response->get_list();
        return $response;
    }

    public function create(ResponseCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->image) {
                $filepath  = Image::image_upload($request->image);
                $request = new Request($request->all());
                $request->merge(['image' => $filepath]);
            }

            $sentiment = $this->comprehend->get_sentiment($request->content);
            $request->merge(['sentiment' => $sentiment]);

            $this->response->create($request);
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
                $filepath  = Image::image_upload($request->image);
                $request = new Request($request->all());
                $request->merge(['image' => $filepath]);
            }
            $this->response->update($request);
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
        try {
            $response = $this->response->remove($id);
            return $response;
        } catch (\Exception  $e) {
            throw $e;
        }
    }
}
