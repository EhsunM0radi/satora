<?php

namespace Webkul\POS\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Webkul\POS\Models\PosReceipt;

class PosReceiptEmail extends Notification
{
    public function __construct(
        public PosReceipt $receipt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Receipt — '.$this->receipt->receipt_number)
            ->line('Thank you for your purchase!')
            ->line('Receipt Number: '.$this->receipt->receipt_number)
            ->line('Total: '.number_format($this->receipt->order?->total ?? 0))
            ->line('')
            ->line(nl2br(e($this->receipt->content_html)))
            ->line('View your receipt online: '.url('/receipts/'.$this->receipt->receipt_number));
    }
}
