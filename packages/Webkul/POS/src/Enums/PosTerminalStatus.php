<?php

namespace Webkul\POS\Enums;

enum PosTerminalStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case MAINTENANCE = 'maintenance';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
