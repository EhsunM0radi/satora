<?php

namespace Webkul\POS\Enums;

enum PosSessionStatus: string
{
    case OPEN = 'open';
    case CLOSING = 'closing';
    case CLOSED = 'closed';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
