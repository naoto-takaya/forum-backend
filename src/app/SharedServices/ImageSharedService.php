<?php

namespace App\SharedServices;

use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Storage;

class ImageSharedService
{
    private $rekognition_client;
    // 画像の節度から作成されるレベル
    private const BAN = 0;    // 登録不可
    private const NORMAL = 1; // 制限なし
    private const BlUR = 2;   // ぼかしをかけて表示

    /**
     * ImageSharedService constructor.
     * @param RekognitionClient $client
     */
    public function __construct(RekognitionClient $client)
    {
        $this->rekognition_client = $client;
    }

    /**
     *
     * @param $request
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function rekognition_forum_image($request)
    {
        $file_name = $this->upload_and_get_file_name($request->image);
        $rekognition_image = Storage::cloud()->get($file_name);
        $confidence = $this->get_confidence($rekognition_image);

        // 危険レベルの査定
        switch (true) {
            case $confidence > 90:
                $level = self::BAN;
                break;
            default:
                $level = self::NORMAL;
                break;
        }
        $request->merge(
            [
                'image_name' => $file_name,
                'confidence' => $confidence,
                'level' => $level
            ]
        );

        return [
            'image_name' => $file_name,
            'confidence' => $confidence,
            'level' => $level
        ];
    }

    /**
     * レスポンスの画像を保存後査定し、危険レベルを作成する。
     * @param $image_file
     * @return int
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function rekognition_response_image($image_file)
    {
        $file_name = $this->upload_and_get_file_name($image_file);
        $rekognition_image = Storage::cloud()->get($file_name);
        $confidence = $this->get_confidence($rekognition_image);

        session(['image_name' => $file_name, 'confidence' => $confidence]);
        // 危険レベルの査定
        switch (true) {
            case $confidence > 90:
                return self::BlUR;
            default:
                return self::NORMAL;
        }
    }

    /**
     * 画像ファイルをS3に保存し、ファイル名を返却する
     * @param $image_file
     * @param string $path
     * @return string
     */
    private function upload_and_get_file_name($image_file, $path = '')
    {
        $file_name = md5(uniqid()) . "." . $image_file->extension();
        Storage::cloud()->putFileAs($path, $image_file, $file_name, 'public');
        return $file_name;
    }

    /**
     * Rekognitionで画像査定し、節度を返却する
     * @param $rekognition_image
     * @return float
     */
    private function get_confidence($rekognition_image)
    {
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
        return $confidence;
    }
}
