<?php

namespace Webkul\POS\Enums;

enum PosCashRegisterType: string
{
    case CASH = 'cash';
    case CARD_TERMINAL = 'card_terminal';
    case MIXED = 'mixed';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
