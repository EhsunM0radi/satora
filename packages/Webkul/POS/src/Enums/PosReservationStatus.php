<?php

namespace Webkul\POS\Enums;

enum PosReservationStatus: string
{
    case RESERVED = 'reserved';
    case CONFIRMED = 'confirmed';
    case RELEASED = 'released';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
