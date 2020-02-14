<?php

namespace App\Http\Controllers;

use App\Http\Requests\RekognitionRequest;
use App\SharedServices\ImageSharedService;

class ImageController extends Controller
{
    private $image;

    public function __construct(ImageSharedService $image)
    {
        $this->image = $image;
    }

    /**
     * 画像を査定し、sessionにファイル名とlevelを保存する
     * @param RekognitionRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function get_forum_image_level(RekognitionRequest $request)
    {
        $level = $this->image->rekognition_forum_image($request->image);
        return response()
            ->json(['level' => $level], 200);
    }

    protected function get_response_image_level(RekognitionRequest $request)
    {
        $level = $this->image->rekognition_response_image($request->image);
        return response()
            ->json(['level' => $level], 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    protected function remove_image_session()
    {
        $this->image->remove_image_session();
        return response()
            ->json(['message' => 'image session has been removed'], 200);
    }
}
