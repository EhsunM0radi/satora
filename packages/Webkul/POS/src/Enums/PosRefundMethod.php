<?php

namespace Webkul\POS\Enums;

enum PosRefundMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case STORE_CREDIT = 'store_credit';
    case WALLET = 'wallet';
    case ORIGINAL_PAYMENT = 'original_payment';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
