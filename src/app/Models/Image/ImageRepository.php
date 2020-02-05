<?php

namespace App\Models\Image;

use App\Models\Image\ImageInterface;
use App\Infrastructure\Image;


class ImageRepository implements ImageInterface
{
    protected $image;

    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    public function get_forum_image($id)
    {
        return $this->image->get_forum_image($id);
    }

    public function create_image($request)
    {
        return $this->image->create_image($request);
    }

    public function update_image($request)
    {
        return $this->image->update_image($request);
    }

    public function remove_image($id)
    {
        return $this->image->remove($id);
    }
}
