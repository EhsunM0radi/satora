<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosOrderCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public $order, public $cashier) {}
}
