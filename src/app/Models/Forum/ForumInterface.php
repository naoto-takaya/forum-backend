<?php

namespace App\Models\Forum;


interface ForumInterface
{
    public function get($id);

    public function get_list();

    public function create($request);

    public function update($request);

    public function remove($id);
}
