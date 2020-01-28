<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;

class NotificationController extends Controller
{
    private $notification_service;

    public function __construct(NotificationService $notification_service)
    {
        $this->notification_service = $notification_service;
    }

    public function list()
    {
        $notifications = $this->notification_service->get_notification_list();
        return response()
            ->json(['notifications' => $notifications,])
            ->setStatusCode(200);
    }

    public function checked_notifications()
    {
        $notifications = $this->notification_service->checked_notifications();
        return response()
            ->json([], 204);
    }
}
