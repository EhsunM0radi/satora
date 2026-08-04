<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosRefundCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public $refund, public $cashier) {}
}
