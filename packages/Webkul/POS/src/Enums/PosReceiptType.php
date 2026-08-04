<?php

namespace Webkul\POS\Enums;

enum PosReceiptType: string
{
    case SALE = 'sale';
    case REFUND = 'refund';
    case EXCHANGE = 'exchange';
    case OPENING = 'opening';
    case CLOSING = 'closing';
    case CASH_MOVEMENT = 'cash_movement';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
