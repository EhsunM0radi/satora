<?php

namespace Webkul\POS\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Webkul\POS\Models\PosSession;

class PosCashDifferenceNotification extends Notification
{
    public function __construct(
        public PosSession $session,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->warning()
            ->subject('Cash Difference Alert — Session '.$this->session->session_number)
            ->line('A cash difference was detected in session '.$this->session->session_number)
            ->line('Expected: '.number_format($this->session->expected_balance))
            ->line('Actual: '.number_format($this->session->closing_balance))
            ->line('Difference: '.number_format($this->session->difference));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'session_number' => $this->session->session_number,
            'expected_balance' => $this->session->expected_balance,
            'closing_balance' => $this->session->closing_balance,
            'difference' => $this->session->difference,
        ];
    }
}
