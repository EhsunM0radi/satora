<?php

namespace Webkul\POS\Enums;

enum PosDiscountType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case BUY_X_GET_Y = 'buy_x_get_y';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
