<?php

namespace App\Services;

use App\Models\Notification\NotificationInterface;

class NotificationService
{
    private $notification;

    public function __construct(NotificationInterface $notification_interface)
    {
        $this->notification = $notification_interface;
    }

    public function get_notification_list()
    {
        return $this->notification->get_notification_list();
    }

    public function get_unchecked_notification_count()
    {
        return $this->notification->get_unchecked_notification_count();
    }

    public function checked_notifications()
    {
        return $this->notification->checked_notifications();
    }
}
