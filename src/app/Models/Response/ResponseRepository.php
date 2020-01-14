<?php

namespace App\Models\Response;

use App\Models\Response\ResponseInterface;
use App\Infrastructure\Response;


class ResponseRepository implements ResponseInterface
{
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function get($id)
    {
        return $this->response->get($id);
    }

    public function get_list()
    {
        return $this->response->get_list();
    }

    public function create($request)
    {
        return $this->response->create($request);
    }

    public function update($request)
    {
        return $this->response->update($request);
    }

    public function remove($id)
    {
        return $this->response->remove($id);
    }
}
