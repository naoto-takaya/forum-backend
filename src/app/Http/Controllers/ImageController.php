<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SharedServices\ImageSharedService;

class ImageController extends Controller
{
    private $image;
    public function __construct(ImageSharedService $image)
    {
        $this->image = $image;
    }

    protected function image_rekognition(Request $request){
        $confidence = $this->image->rekognition_save($request->image);
        return response()
            ->json(['confidence' =>$confidence], 200);
    }
}
