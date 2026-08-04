<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosSessionClosed
{
    use Dispatchable, SerializesModels;

    public function __construct(public $session, public $cashier, public ?float $closingBalance, public ?float $difference) {}
}
