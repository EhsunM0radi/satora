<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosSessionSuspended
{
    use Dispatchable, SerializesModels;

    public function __construct(public $session, public $cashier) {}
}
