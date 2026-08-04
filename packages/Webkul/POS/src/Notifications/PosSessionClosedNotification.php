<?php

namespace Webkul\POS\Notifications;

use Illuminate\Notifications\Notification;
use Webkul\POS\Models\PosSession;

class PosSessionClosedNotification extends Notification
{
    public function __construct(
        public PosSession $session,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'session_number' => $this->session->session_number,
            'opening_balance' => $this->session->opening_balance,
            'closing_balance' => $this->session->closing_balance,
            'difference' => $this->session->difference,
            'total_orders' => $this->session->orders()->where('status', 'completed')->count(),
            'total_revenue' => $this->session->orders()->where('status', 'completed')->sum('total'),
            'closed_at' => $this->session->closed_at->toIso8601String(),
        ];
    }
}
