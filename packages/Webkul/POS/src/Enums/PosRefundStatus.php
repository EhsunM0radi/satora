<?php

namespace Webkul\POS\Enums;

enum PosRefundStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
