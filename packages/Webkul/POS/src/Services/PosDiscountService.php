<?php

namespace Webkul\POS\Services;

use Illuminate\Support\Facades\Gate;
use Webkul\POS\Events\PosDiscountApplied;
use Webkul\POS\Events\PosDiscountRemoved;
use Webkul\POS\Models\PosDiscount;
use Webkul\POS\Models\PosOrder;
use Webkul\POS\Repositories\PosDiscountRepository;

class PosDiscountService
{
    public function __construct(
        protected PosDiscountRepository $discountRepository,
    ) {}

    public function apply(PosOrder $order, string $code): float
    {
        Gate::authorize('pos.apply_discount');

        $discount = $this->discountRepository->findWhere([
            'code' => $code,
            'is_active' => true,
        ])->first();

        if (! $discount) {
            return 0;
        }

        if (! $this->validateDiscount($discount, $order)) {
            return 0;
        }

        $discountAmount = $this->calculateDiscountAmount($discount, $order);

        $order->update([
            'discount_amount' => $order->discount_amount + $discountAmount,
            'total' => $order->total - $discountAmount,
            'due_amount' => $order->due_amount - $discountAmount,
        ]);

        $discount->increment('usage_count');

        event(new PosDiscountApplied($order, $discount, $discountAmount));

        return $discountAmount;
    }

    public function removeDiscount(PosOrder $order, PosDiscount $discount): void
    {
        $discountAmount = $this->calculateDiscountAmount($discount, $order);

        $order->update([
            'discount_amount' => max(0, $order->discount_amount - $discountAmount),
            'total' => $order->total + $discountAmount,
            'due_amount' => $order->due_amount + $discountAmount,
        ]);

        $discount->decrement('usage_count');

        event(new PosDiscountRemoved($order, $discount));
    }

    public function applyManualDiscount(PosOrder $order, float $amount, string $type = 'fixed'): void
    {
        Gate::authorize('pos.apply_discount');

        $discountAmount = $type === 'percentage'
            ? $order->subtotal * ($amount / 100)
            : $amount;

        $order->update([
            'discount_amount' => $order->discount_amount + $discountAmount,
            'total' => $order->total - $discountAmount,
            'due_amount' => $order->due_amount - $discountAmount,
        ]);
    }

    public function calculateDiscountAmount(PosDiscount $discount, PosOrder $order): float
    {
        $base = $discount->applies_to === 'order' ? $order->subtotal : $order->subtotal;

        $amount = $discount->type === 'percentage'
            ? $base * ($discount->value / 100)
            : $discount->value;

        if ($discount->max_discount_amount !== null) {
            $amount = min($amount, $discount->max_discount_amount);
        }

        return round($amount, 4);
    }

    protected function validateDiscount(PosDiscount $discount, PosOrder $order): bool
    {
        if ($discount->min_order_amount && $order->subtotal < $discount->min_order_amount) {
            return false;
        }

        if ($discount->usage_limit && $discount->usage_count >= $discount->usage_limit) {
            return false;
        }

        if ($discount->starts_at && now()->lt($discount->starts_at)) {
            return false;
        }

        if ($discount->ends_at && now()->gt($discount->ends_at)) {
            return false;
        }

        return true;
    }
}
