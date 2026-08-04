<?php

namespace Webkul\POS\Services;

use Webkul\POS\Events\PosMarketplaceCommissionCalculated;
use Webkul\POS\Events\PosMarketplaceOrderCreated;
use Webkul\POS\Models\PosOrder;

class PosMarketplaceService
{
    public function isMarketplaceMode(): bool
    {
        $tenant = app('current_tenant');

        return $tenant && ($tenant->settings['mode'] ?? 'store') === 'marketplace';
    }

    public function assignSeller(PosOrder $order, int $sellerId): PosOrder
    {
        $order->update(['seller_id' => $sellerId]);

        $commission = $this->calculateCommission($order);

        event(new PosMarketplaceOrderCreated($order, $sellerId, $commission));

        return $order;
    }

    public function calculateCommission(PosOrder $order): float
    {
        if (! $order->seller_id) {
            return 0;
        }

        $rate = config('pos.marketplace.default_commission_rate', 10.0);
        $calculation = config('pos.marketplace.commission_calculation', 'order_total');

        $base = $calculation === 'order_total' ? $order->total : $order->subtotal;
        $commission = $base * ($rate / 100);

        event(new PosMarketplaceCommissionCalculated($order, null, $commission));

        return round($commission, 4);
    }

    public function getSellerNetAmount(PosOrder $order): float
    {
        return $order->total - $this->calculateCommission($order);
    }

    public function getSellerOrders(int $sellerId, array $filters = []): array
    {
        $query = PosOrder::where('seller_id', $sellerId)
            ->where('status', 'completed')
            ->with(['items', 'payments']);

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'commission' => $this->calculateCommission($order),
                'net_amount' => $this->getSellerNetAmount($order),
                'created_at' => $order->created_at,
            ];
        })->toArray();
    }
}
