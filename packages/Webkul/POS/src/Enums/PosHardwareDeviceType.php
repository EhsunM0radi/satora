<?php

namespace Webkul\POS\Enums;

enum PosHardwareDeviceType: string
{
    case BARCODE_SCANNER = 'barcode_scanner';
    case RECEIPT_PRINTER = 'receipt_printer';
    case CASH_DRAWER = 'cash_drawer';
    case CUSTOMER_DISPLAY = 'customer_display';
    case WEIGHT_SCALE = 'weight_scale';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
