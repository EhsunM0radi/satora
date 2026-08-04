<?php

namespace Webkul\POS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\POS\Services\PosInventoryService;

class CleanupExpiredReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(PosInventoryService $inventoryService): void
    {
        $released = $inventoryService->releaseExpiredReservations();

        if ($released > 0) {
            \Log::info("POS: Released {$released} expired inventory reservations.");
        }
    }
}
