<?php

namespace App\Models\Forum;

use App\Models\Forum\ForumInterface;
use App\Infrastructure\Forum;


class ForumRepository implements ForumInterface
{
    protected $forum;

    public function __construct(Forum $forum)
    {
        $this->forum = $forum;
    }

    public function get($id)
    {
        return $this->forum->get($id);
    }

    public function get_list()
    {
        return $this->forum->get_list();
    }

    public function create($request)
    {
        return $this->forum->create($request);
    }

    public function update($request)
    {
        return $this->forum->update($request);
    }

    public function delete($id)
    {
        return $this->forum->delete($id);
    }
}
