<?php

namespace Webkul\POS\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PosFraudAlertNotification extends Notification
{
    public function __construct(
        public array $fraudData,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Fraud Alert — POS Activity')
            ->line('Suspicious POS activity detected.')
            ->line('Risk Level: '.$this->fraudData['risk'])
            ->line('Risk Score: '.$this->fraudData['score'])
            ->line('Flags: '.implode(', ', $this->fraudData['flags']));
    }

    public function toArray(object $notifiable): array
    {
        return $this->fraudData;
    }
}
