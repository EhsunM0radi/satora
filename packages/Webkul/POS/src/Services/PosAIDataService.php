<?php

namespace Webkul\POS\Services;

use Illuminate\Support\Facades\DB;
use Webkul\POS\Jobs\CollectAITrainingData;

class PosAIDataService
{
    public function collectOrderEvent(array $data): void
    {
        if (! config('pos.ai.data_collection_enabled', true)) {
            return;
        }

        // Queue AI training data for later processing
        dispatch(new CollectAITrainingData([
            'type' => 'order',
            'tenant_id' => app('current_tenant')?->id,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ]));
    }

    public function getSalesHistory(int $productId, int $days = 90): array
    {
        return DB::table('pos_order_items')
            ->join('pos_orders', 'pos_order_items.pos_order_id', '=', 'pos_orders.id')
            ->where('pos_order_items.product_id', $productId)
            ->where('pos_orders.status', 'completed')
            ->where('pos_orders.created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(pos_orders.created_at) as date'),
                DB::raw('SUM(pos_order_items.quantity) as quantity'),
                DB::raw('SUM(pos_order_items.total) as revenue')
            )
            ->groupBy(DB::raw('DATE(pos_orders.created_at)'))
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function getCustomerPurchasePattern(int $customerId): array
    {
        return PosOrder::where('customer_id', $customerId)
            ->where('status', 'completed')
            ->with('items')
            ->get()
            ->groupBy(fn ($o) => $o->created_at->format('Y-m'))
            ->map(fn ($orders) => [
                'order_count' => $orders->count(),
                'total_spent' => $orders->sum('total'),
                'product_ids' => $orders->flatMap->items->pluck('product_id')->unique()->values(),
            ])
            ->toArray();
    }

    public function getPopularProducts(int $tenantId, int $limit = 20): array
    {
        return DB::table('pos_order_items')
            ->join('pos_orders', 'pos_order_items.pos_order_id', '=', 'pos_orders.id')
            ->where('pos_orders.tenant_id', $tenantId)
            ->where('pos_orders.status', 'completed')
            ->where('pos_orders.created_at', '>=', now()->subDays(30))
            ->select(
                'pos_order_items.product_id',
                DB::raw('SUM(pos_order_items.quantity) as total_sold'),
                DB::raw('COUNT(DISTINCT pos_orders.id) as order_count')
            )
            ->groupBy('pos_order_items.product_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function detectFraudRisk(PosOrder $order): array
    {
        if (! config('pos.ai.fraud_detection_enabled', true)) {
            return ['risk' => 'low', 'score' => 0];
        }

        $riskScore = 0;
        $flags = [];

        // Rule 1: High-value order
        if ($order->total > 10000000) {
            $riskScore += 20;
            $flags[] = 'high_value';
        }

        // Rule 2: Multiple orders in short time from same cashier
        $recentOrders = PosOrder::where('admin_user_id', $order->admin_user_id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();
        if ($recentOrders > 5) {
            $riskScore += 15;
            $flags[] = 'rapid_orders';
        }

        // Rule 3: Unusual discount
        if ($order->discount_amount > ($order->subtotal * 0.5)) {
            $riskScore += 25;
            $flags[] = 'excessive_discount';
        }

        // Rule 4: Void after completion pattern
        $voidCount = PosOrder::where('customer_id', $order->customer_id)
            ->where('status', 'voided')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
        if ($voidCount > 2) {
            $riskScore += 30;
            $flags[] = 'void_pattern';
        }

        return [
            'risk' => $riskScore > 50 ? 'high' : ($riskScore > 25 ? 'medium' : 'low'),
            'score' => $riskScore,
            'flags' => $flags,
        ];
    }
}
