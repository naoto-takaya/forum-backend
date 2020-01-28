<?php

namespace App\Models\Response;


interface ResponseInterface
{
    public function get_response($id);

    public function get_replies($id);

    public function get_response_list();

    public function create_response($request);

    public function update_response($request);

    public function remove_response($id);

    public function create_notification_reply($request);
}
