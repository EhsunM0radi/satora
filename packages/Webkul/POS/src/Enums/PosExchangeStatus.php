<?php

namespace Webkul\POS\Enums;

enum PosExchangeStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
