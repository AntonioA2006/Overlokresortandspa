<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $hotelName = (string) config('overlook.hotel_name');

        return (new MailMessage)
            ->subject(__('auth.verify_mail_subject', ['hotel' => $hotelName]))
            ->markdown('mail.auth.verify-email', [
                'url' => $this->verificationUrl($notifiable),
                'userName' => $notifiable->name,
                'hotelName' => $hotelName,
            ]);
    }
}
