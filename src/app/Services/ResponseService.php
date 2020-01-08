<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Response\ResponseInterface;
use App\Http\Requests\ResponseCreateRequest;
use App\Http\Requests\ResponseUpdateRequest;

class ResponseService
{
    private $response;

    public function __construct(ResponseInterface $response_interface)
    {
        $this->response = $response_interface;
    }

    public function get($id)
    {
        try {
            $response = $this->response->get($id);
            return $response;
        } catch (\Exception  $e) {
            throw $e;
        }
    }

    public function get_list()
    {
        try {
            $response = $this->response->get_list();
            return $response;
        } catch (\Exception  $e) {
            throw $e;
        }
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

    public function delete($id)
    {
        try {
            $response = $this->response->delete($id);
            return $response;
        } catch (\Exception  $e) {
            throw $e;
        }
    }
}
