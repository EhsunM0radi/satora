<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosPaymentReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(public $payment, public $order) {}
}
