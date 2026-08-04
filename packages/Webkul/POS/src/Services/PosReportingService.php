<?php

namespace Webkul\POS\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\POS\Models\PosOrder;
use Webkul\POS\Models\PosPayment;
use Webkul\POS\Models\PosRefund;
use Webkul\POS\Models\PosSession;

class PosReportingService
{
    public function dailySalesReport(?string $date = null): array
    {
        $date = $date ? Carbon::parse($date) : today();

        $orders = PosOrder::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->get();

        return [
            'date' => $date->toDateString(),
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total'),
            'total_discounts' => $orders->sum('discount_amount'),
            'total_tax' => $orders->sum('tax_amount'),
            'average_order' => $orders->count() > 0 ? $orders->avg('total') : 0,
            'payment_breakdown' => $this->paymentBreakdown($date),
            'hourly_breakdown' => $this->hourlyBreakdown($date),
        ];
    }

    public function cashierPerformanceReport(string $from, string $to): array
    {
        $orders = PosOrder::whereBetween('created_at', [Carbon::parse($from), Carbon::parse($to)])
            ->where('status', 'completed')
            ->with('cashier')
            ->get();

        $cashiers = $orders->groupBy('admin_user_id')->map(function ($group) {
            $cashier = $group->first()->cashier;

            return [
                'cashier_id' => $group->first()->admin_user_id,
                'cashier_name' => $cashier?->name ?? 'Unknown',
                'total_orders' => $group->count(),
                'total_revenue' => $group->sum('total'),
                'total_refunds' => PosRefund::where('admin_user_id', $group->first()->admin_user_id)
                    ->whereBetween('created_at', [Carbon::parse($from), Carbon::parse($to)])
                    ->sum('total_amount'),
                'average_order' => $group->avg('total'),
            ];
        })->values();

        return [
            'from' => $from,
            'to' => $to,
            'cashiers' => $cashiers->toArray(),
        ];
    }

    public function productPerformanceReport(string $from, string $to, int $limit = 50): array
    {
        return DB::table('pos_order_items')
            ->join('pos_orders', 'pos_order_items.pos_order_id', '=', 'pos_orders.id')
            ->where('pos_orders.status', 'completed')
            ->whereBetween('pos_orders.created_at', [Carbon::parse($from), Carbon::parse($to)])
            ->select(
                'pos_order_items.product_id',
                'pos_order_items.name',
                DB::raw('SUM(pos_order_items.quantity) as total_quantity'),
                DB::raw('SUM(pos_order_items.total) as total_revenue'),
                DB::raw('COUNT(DISTINCT pos_orders.id) as order_count')
            )
            ->groupBy('pos_order_items.product_id', 'pos_order_items.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function paymentReport(string $from, string $to): array
    {
        return PosPayment::whereBetween('created_at', [Carbon::parse($from), Carbon::parse($to)])
            ->where('status', 'approved')
            ->select(
                'payment_method_code',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('payment_method_code')
            ->get()
            ->toArray();
    }

    public function refundReport(string $from, string $to): array
    {
        $refunds = PosRefund::where('status', 'completed')
            ->whereBetween('created_at', [Carbon::parse($from), Carbon::parse($to)])
            ->get();

        return [
            'total_refunds' => $refunds->count(),
            'total_amount' => $refunds->sum('total_amount'),
            'by_method' => $refunds->groupBy('refund_method')->map(fn ($g) => [
                'count' => $g->count(),
                'amount' => $g->sum('total_amount'),
            ])->toArray(),
        ];
    }

    public function sessionSummary(int $sessionId): array
    {
        $session = PosSession::with(['orders', 'cashMovements'])->findOrFail($sessionId);

        return [
            'session_number' => $session->session_number,
            'opened_at' => $session->opened_at,
            'closed_at' => $session->closed_at,
            'opening_balance' => $session->opening_balance,
            'closing_balance' => $session->closing_balance,
            'expected_balance' => $session->expected_balance,
            'difference' => $session->difference,
            'total_orders' => $session->orders->count(),
            'total_revenue' => $session->orders->where('status', 'completed')->sum('total'),
            'cash_movements' => $session->cashMovements->groupBy('type')->map(fn ($g) => $g->sum('amount'))->toArray(),
        ];
    }

    public function inventoryMovementReport(string $from, string $to): array
    {
        return DB::table('pos_inventory_reservations')
            ->whereBetween('created_at', [Carbon::parse($from), Carbon::parse($to)])
            ->select(
                'product_id',
                'status',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(*) as movement_count')
            )
            ->groupBy('product_id', 'status')
            ->get()
            ->toArray();
    }

    protected function paymentBreakdown(Carbon $date): array
    {
        return PosPayment::whereDate('created_at', $date)
            ->where('status', 'approved')
            ->select('payment_method_code', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method_code')
            ->get()
            ->toArray();
    }

    protected function hourlyBreakdown(Carbon $date): array
    {
        $orders = PosOrder::whereDate('created_at', $date)
            ->where('status', 'completed')
            ->get();

        $hourly = [];
        for ($h = 0; $h < 24; $h++) {
            $hourOrders = $orders->filter(fn ($o) => $o->created_at->hour === $h);
            $hourly[$h] = [
                'count' => $hourOrders->count(),
                'revenue' => $hourOrders->sum('total'),
            ];
        }

        return $hourly;
    }
}
