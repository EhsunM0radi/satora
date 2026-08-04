<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosMarketplaceOrderCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public $order, public int $sellerId, public float $commission) {}
}
