<?php

namespace App\Models\Notification;


interface NotificationInterface
{
    public function get_notification_list();

    public function get_unchecked_notification_count();

    public function checked_notifications();
}
