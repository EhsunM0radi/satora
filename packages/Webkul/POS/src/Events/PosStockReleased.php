<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosStockReleased
{
    use Dispatchable, SerializesModels;

    public function __construct(public $reservation, public $product) {}
}
