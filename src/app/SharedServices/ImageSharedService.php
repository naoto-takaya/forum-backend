<?php

namespace App\SharedServices;

use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Storage;

class ImageSharedService
{
    private $rekognition_client;

    /**
     * ImageSharedService constructor.
     * @param RekognitionClient $client
     */
    public function __construct(RekognitionClient $client)
    {
        $this->rekognition_client = $client;
    }

    /**
     * リクエストされた画像に名前をつけて保存し、Rekognitionに査定させ、節度を返却する
     * @param $image_file
     * @return int
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function rekognition_save($image_file)
    {
        $file_name = md5(uniqid()) . "." . $image_file->extension();
        Storage::cloud()->putFileAs('', $image_file, $file_name, 'public');
        $rekognition_image = Storage::cloud()->get($file_name);

        $result = $this->rekognition_client->detectModerationLabels([
            'Image' => [
                'Bytes' => $rekognition_image,
            ],
            'MinConfidence' => 80,
        ])['ModerationLabels'];
        $confidence = 0;
        foreach ($result as $factor) {
            if ($confidence <= $factor['Confidence']) {
                $confidence = $factor['Confidence'];
            }
        }

        session(['image_name' => $file_name, 'confidence' => $confidence]);
        return $confidence;
    }
}
