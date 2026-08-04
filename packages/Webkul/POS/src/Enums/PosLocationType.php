<?php

namespace Webkul\POS\Enums;

enum PosLocationType: string
{
    case STORE = 'store';
    case WAREHOUSE = 'warehouse';
    case POPUP = 'popup';
    case MOBILE = 'mobile';

    public function label(): string
    {
        return __('pos::app.enums.'.strtolower($this->name));
    }
}
