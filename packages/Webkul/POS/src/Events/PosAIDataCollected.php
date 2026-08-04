<?php

namespace Webkul\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosAIDataCollected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $entityType,
        public int $entityId,
        public string $eventType,
        public int $tenantId,
        public array $payload,
        public mixed $timestamp,
    ) {}
}
