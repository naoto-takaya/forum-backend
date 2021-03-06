<?php

namespace App\Models\Response;

use App\Infrastructure\Image;
use App\Infrastructure\Notification;
use App\Infrastructure\Response;


class ResponseRepository implements ResponseInterface
{
    private $response;
    private $notification;
    private $image;

    public function __construct(Response $response, Notification $notification, Image $image)
    {
        $this->response = $response;
        $this->notification = $notification;
        $this->image = $image;
    }

    public function get_response($id)
    {
        return $this->response->get_response($id);
    }

    public function get_replies($id)
    {
        return $this->response->get_replies($id);
    }

    public function get_response_list($forum_id)
    {
        return $this->response->get_response_list($forum_id);
    }

    public function create_response($request)
    {
        return $this->response->create_response($request);
    }

    public function update_response($request)
    {
        return $this->response->update_response($request);
    }

    public function remove_response($id)
    {
        $this->response->remove_response($id);
    }

    public function create_image($request)
    {
        $this->image->create_response_image($request);
    }

    public function update_image($request)
    {
        $this->image->update_response_image($request);
    }

    public function delete_image($response_id)
    {
        $this->image->delete_response_image($response_id);
    }

    public function create_notification_reply($response_id)
    {
        $this->notification->create_notification_reply($response_id);
    }
}
