<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosStockDeducted
{
    use Dispatchable, SerializesModels;

    public function __construct(public $orderItem, public $product, public float $quantity) {}
}
