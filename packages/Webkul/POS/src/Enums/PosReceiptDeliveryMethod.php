<?php

namespace Webkul\POS\Enums;

enum PosReceiptDeliveryMethod: string
{
    case PRINT = 'print';
    case EMAIL = 'email';
    case SMS = 'sms';
    case DIGITAL = 'digital';
    case NONE = 'none';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
