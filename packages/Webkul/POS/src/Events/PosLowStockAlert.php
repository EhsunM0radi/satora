<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosLowStockAlert
{
    use Dispatchable, SerializesModels;

    public function __construct(public $product, public float $currentStock, public float $threshold) {}
}
