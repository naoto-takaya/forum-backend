<?php

namespace App\SharedServices;

use App\Models\Image\ImageInterface;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Storage;

class ImageSharedService
{
    private $rekognition_client;
    private $image_interface;

    /**
     * ImageSharedService constructor.
     * @param RekognitionClient $client
     * @param ImageInterface $image_interface
     */
    public function __construct(RekognitionClient $client, ImageInterface $image_interface)
    {
        $this->rekognition_client = $client;
        $this->image_interface = $image_interface;
    }

    public function rekognition_save($image_file)
    {
        $filename = md5(uniqid()) . "." . $image_file->extension();
        Storage::cloud()->putFileAs('', $image_file, $filename, 'public');

        $result = $this->rekognition_client->detectModerationLabels([
            'Image' => [
                'Bytes' => $image_file,
            ],
            'MinConfidence' => 80,
        ])['ModerationLabels'];
        $confidence = 0;
        foreach ($result as $factor) {
            if ($confidence <= $factor['Confidence']) {
                $confidence = $factor['Confidence'];
            }
        }

        session(['image_name' => $filename, 'confidence' => $confidence]);
        return $confidence;
    }
}
