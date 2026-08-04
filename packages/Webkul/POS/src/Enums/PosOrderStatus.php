<?php

namespace Webkul\POS\Enums;

enum PosOrderStatus: string
{
    case DRAFT = 'draft';
    case HELD = 'held';
    case COMPLETED = 'completed';
    case VOIDED = 'voided';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case EXCHANGED = 'exchanged';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
