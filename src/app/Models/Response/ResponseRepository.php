<?php

namespace App\Models\Response;

use App\Models\Response\ResponseInterface;
use App\Infrastructure\Response;
use App\Infrastructure\Notification;
use App\Infrastructure\Image;
use Illuminate\Auth\AuthenticationException;


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

    public function get_response_list()
    {
        return $this->response->get_response_list();
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

    public function create_image($response_id)
    {
        $this->image->create_response_image($response_id);
    }

    public function update_image($response_id)
    {
        $this->image->update_response_image($response_id);
    }

    public function create_notification_reply($response_id)
    {
        $this->notification->create_notification_reply($response_id);
    }
}
