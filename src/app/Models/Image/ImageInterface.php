<?php

namespace App\Models\Image;


interface ImageInterface
{
    public function get_forum_image($id);

    public function create_image($request);

    public function update_image($request);

    public function remove_image($id);
}
