<?php

namespace App\Models\Forum;


interface ForumInterface
{
    public function get($id);

    public function get_forum_list();

    public function create_forum($request);

    public function update_forum($request);

    public function create_image($request);

    public function update_image($request);

    public function remove($id);
}
