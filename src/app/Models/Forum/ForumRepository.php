<?php

namespace App\Models\Forum;

use App\Infrastructure\Forum;
use App\Infrastructure\Image;


class ForumRepository implements ForumInterface
{
    private $forum;
    private $image;

    public function __construct(Forum $forum, Image $image)
    {
        $this->forum = $forum;
        $this->image = $image;
    }

    public function get($id)
    {
        return $this->forum->get($id);
    }

    public function get_forum_list()
    {
        return $this->forum->get_forum_list();
    }

    public function create_forum($request)
    {
        return $this->forum->create_forum($request);
    }

    public function update_forum($request)
    {
        return $this->forum->update_forum($request);
    }

    public function create_image($request)
    {
        return $this->image->create_forum_image($request);
    }

    public function update_image($request)
    {
        return $this->image->update_forum_image($request);
    }

    public function remove($id)
    {
        return $this->forum->remove($id);
    }
}
