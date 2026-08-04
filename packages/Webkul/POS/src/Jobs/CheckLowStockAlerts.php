<?php

namespace Webkul\POS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\POS\Events\PosLowStockAlert;

class CheckLowStockAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $products = \DB::table('products')
            ->whereNotNull('min_qty')
            ->get();

        foreach ($products as $product) {
            $available = \DB::table('inventory_sources')
                ->where('product_id', $product->id)
                ->sum('qty');

            if ($available <= $product->min_qty) {
                event(new PosLowStockAlert(
                    $product,
                    $available,
                    $product->min_qty
                ));
            }
        }
    }
}
