<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosCashMovementCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public $cashMovement, public $register) {}
}
