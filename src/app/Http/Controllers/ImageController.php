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
     * 画像を査定し、sessionにファイル名とconfidenceを保存する
     * @param RekognitionRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function image_rekognition(RekognitionRequest $request)
    {
        $confidence = $this->image->rekognition_save($request->image);
        return response()
            ->json(['confidence' => $confidence], 200);
    }
}
