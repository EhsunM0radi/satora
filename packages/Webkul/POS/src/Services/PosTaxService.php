<?php

namespace Webkul\POS\Services;

use Webkul\POS\Models\PosOrder;

class PosTaxService
{
    public function calculate(PosOrder $order, float $taxableAmount): float
    {
        if ($order->tax_inclusive) {
            return 0; // Tax already included in prices
        }

        $items = $order->items()->get();
        $taxAmount = 0;

        foreach ($items as $item) {
            $itemTaxable = $item->total * ($taxableAmount / max($order->subtotal, 0.01));
            $taxAmount += $itemTaxable * ($item->tax_rate / 100);
        }

        return round($taxAmount, 4);
    }
}
