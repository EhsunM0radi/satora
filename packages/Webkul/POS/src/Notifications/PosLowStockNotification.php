<?php

namespace Webkul\POS\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PosLowStockNotification extends Notification
{
    public function __construct(
        public mixed $product,
        public float $currentStock,
        public float $threshold,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->warning()
            ->subject('Low Stock Alert: '.($this->product->name ?? 'Product'))
            ->line('A product has reached its low stock threshold.')
            ->line('Product: '.($this->product->name ?? 'Unknown'))
            ->line('Current Stock: '.$this->currentStock)
            ->line('Threshold: '.$this->threshold);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product?->id,
            'product_name' => $this->product?->name ?? 'Unknown',
            'current_stock' => $this->currentStock,
            'threshold' => $this->threshold,
        ];
    }
}
