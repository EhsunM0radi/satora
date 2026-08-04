<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosCashDrawerOpened
{
    use Dispatchable, SerializesModels;

    public function __construct(public $register, public $cashier, public ?string $reason) {}
}
