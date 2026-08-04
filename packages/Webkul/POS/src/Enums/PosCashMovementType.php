<?php

namespace Webkul\POS\Enums;

enum PosCashMovementType: string
{
    case OPENING = 'opening';
    case CLOSING = 'closing';
    case CASH_IN = 'cash_in';
    case CASH_OUT = 'cash_out';
    case SALE = 'sale';
    case REFUND = 'refund';
    case EXPENSE = 'expense';
    case DEPOSIT = 'deposit';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
