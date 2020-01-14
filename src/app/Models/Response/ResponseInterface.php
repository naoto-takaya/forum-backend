<?php

namespace App\Models\Response;


interface ResponseInterface
{
    public function get($id);

    public function get_list();

    public function create($request);

    public function update($request);

    public function remove($id);
}
