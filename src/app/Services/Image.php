<?php

namespace App\Services;

require 'vendor/autoload.php';

use Illuminate\Support\Facades\Storage;
use Aws\Rekognition\RekognitionClient;

class Image
{

    /**
     * @param $image_file jpge,png,gif等画像ファイル
     */
    public function __construct($image_file)
    {
        $this->image = $image_file;
    }

    /**
     * ファイル名(ランダム文字列)を付加し、S3へアップロードを行う
     * @param $image_file jpge,png,gif等画像ファイル
     * @return $filepath
     */
    static public function image_upload($image_file)
    {
        $filename = md5(uniqid()) . "." . $image_file->extension();
        $filepath = Storage::cloud()->putFileAs('', $image_file, $filename, 'public');

        return $filepath;
    }

    /**
     * 画像ファイルを削除する
     * @param $filepath S3に保存したファイル名
     */
    static public function image_delete($filepath)
    {
        if (Storage::cloud()->exists($filepath)) {
            Storage::cloud()->delete($filepath);

            return true;
        } else {
            return false;
        }
    }

    /**
     * 指定した画像の節度を分析する
     * @param $iamge_file 画像ファイル
     * @return $result Rekognitionの返却値
     */
    static public function rekognition_image($image_file)
    {
        $client = new RekognitionClient([
            'region' => 'us-east-1',
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $result = $client->detectModerationLabels([
            'Image' => [
                'Bytes' => $image_file,
            ],
            'MinConfidence' => 90,
        ]);

        return $result;
    }
}
