<?php

namespace Webkul\POS\Enums;

enum PosPaymentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case DECLINED = 'declined';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
