<?php

namespace App\Models\Notification;

use App\Models\Notification\NotificationInterface;
use App\Infrastructure\Notification;

class NotificationRepository implements NotificationInterface
{
    protected $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
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
        return $this->notification->notification_checked();
    }
}
