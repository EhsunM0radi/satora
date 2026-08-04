<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosPaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(public $payment, public $order, public string $error) {}
}
