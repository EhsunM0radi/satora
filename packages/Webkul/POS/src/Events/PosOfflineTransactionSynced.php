<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosOfflineTransactionSynced
{
    use Dispatchable, SerializesModels;

    public function __construct(public $queueItem, public int $serverId) {}
}
