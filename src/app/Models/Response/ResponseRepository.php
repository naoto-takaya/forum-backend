<?php

namespace App\Models\Response;

use App\Models\Response\ResponseInterface;
use App\Infrastructure\Response;
use App\Infrastructure\Notification;
use Illuminate\Auth\AuthenticationException;


class ResponseRepository implements ResponseInterface
{
    protected $response;
    protected $notification;

    public function __construct(Response $response, Notification $notification)
    {
        $this->response = $response;
        $this->notification = $notification;
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
        $this->response->create_response($request);
    }

    public function update_response($request)
    {
        $this->response->update_response($request);
    }

    public function remove_response($id)
    {
        $this->response->remove_response($id);
    }

    public function create_notification_reply($request)
    {
        $this->notification->create_notification_reply($request);
    }
}
