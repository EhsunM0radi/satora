<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosExchangeCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public $exchange, public $originalOrder, public $newOrder) {}
}
