<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hotelName = (string) config('overlook.hotel_name');
        $expireMinutes = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject(__('auth.reset_mail_subject', ['hotel' => $hotelName]))
            ->markdown('mail.auth.reset-password', [
                'url' => $this->resetUrl($notifiable),
                'userName' => $notifiable->name,
                'expire' => $expireMinutes,
                'hotelName' => $hotelName,
            ]);
    }

    public function resetUrl(object $notifiable): string
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
