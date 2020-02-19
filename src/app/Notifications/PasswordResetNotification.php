<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public $token;
    protected $title = 'パスワードの再設定';

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(env('APP_FRONT_URL') . '/password/reset/' . $this->token . '?email=' . $notifiable->getEmailForPasswordReset());
        return (new MailMessage)
            ->subject($this->title)
            ->line('パスワードのリセット申請を受け付けました。下記のリンクからパスワードを再設定してください')
            ->action('パスワードリセット', $url)
            ->markdown(
                'vendor.notifications.email',
                [
                    'url' => url('password/reset/', $this->token),
                ]
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
