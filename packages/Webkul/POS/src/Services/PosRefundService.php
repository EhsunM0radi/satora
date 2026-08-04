<?php

namespace Webkul\POS\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Webkul\POS\Events\PosRefundCompleted;
use Webkul\POS\Events\PosRefundInitiated;
use Webkul\POS\Events\PosRefundRejected;
use Webkul\POS\Models\PosOrder;
use Webkul\POS\Models\PosRefund;
use Webkul\POS\Repositories\PosRefundItemRepository;
use Webkul\POS\Repositories\PosRefundRepository;

class PosRefundService
{
    public function __construct(
        protected PosRefundRepository $refundRepository,
        protected PosRefundItemRepository $refundItemRepository,
        protected PosPaymentService $paymentService,
        protected PosInventoryService $inventoryService,
        protected PosReceiptService $receiptService,
    ) {}

    public function initiateRefund(PosOrder $order, array $items, string $refundMethod = 'original_payment', ?string $reason = null): PosRefund
    {
        Gate::authorize('pos.process_refund');

        $totalAmount = 0;

        $refund = DB::transaction(function () use ($order, $items, $refundMethod, $reason, &$totalAmount) {
            $refundNumber = $this->generateRefundNumber($order);

            $refund = $this->refundRepository->create([
                'pos_order_id' => $order->id,
                'pos_session_id' => $order->pos_session_id,
                'admin_user_id' => auth('admin')->id(),
                'refund_number' => $refundNumber,
                'refund_method' => $refundMethod,
                'total_amount' => 0,
                'reason' => $reason,
                'status' => 'pending',
            ]);

            foreach ($items as $itemData) {
                $orderItem = $order->items()->findOrFail($itemData['order_item_id']);
                $quantity = $itemData['quantity'];
                $amount = ($orderItem->unit_price - ($orderItem->discount_amount / max($orderItem->quantity, 1))) * $quantity;

                $this->refundItemRepository->create([
                    'pos_refund_id' => $refund->id,
                    'pos_order_item_id' => $orderItem->id,
                    'quantity' => $quantity,
                    'amount' => $amount,
                    'reason' => $itemData['reason'] ?? null,
                    'restock' => $itemData['restock'] ?? true,
                ]);

                $totalAmount += $amount;
            }

            $refund->update(['total_amount' => $totalAmount]);

            event(new PosRefundInitiated($refund, auth('admin')->user()));

            return $refund;
        });

        return $refund;
    }

    public function completeRefund(PosRefund $refund): PosRefund
    {
        return DB::transaction(function () use ($refund) {
            // Process payment refund
            if ($refund->refund_method === 'original_payment') {
                $payments = $refund->order->payments()->where('status', 'approved')->get();
                $remaining = $refund->total_amount;

                foreach ($payments as $payment) {
                    if ($remaining <= 0) {
                        break;
                    }
                    $refundAmount = min($remaining, $payment->amount);
                    $this->paymentService->refundPayment($payment, $refundAmount);
                    $remaining -= $refundAmount;
                }
            }

            // Restock inventory
            foreach ($refund->items as $refundItem) {
                if ($refundItem->restock) {
                    $orderItem = $refundItem->orderItem;
                    $this->inventoryService->returnStock(
                        $orderItem->product_id,
                        $refundItem->quantity,
                        $orderItem->inventory_source_id
                    );
                }

                // Mark original order item as refunded
                $orderItem = $refundItem->orderItem;
                $orderItem->update([
                    'is_refunded' => true,
                    'refunded_quantity' => $orderItem->refunded_quantity + $refundItem->quantity,
                ]);
            }

            // Update order status
            $order = $refund->order;
            $allRefunded = $order->items()->where('is_refunded', false)->doesntExist();
            $order->update([
                'status' => $allRefunded ? 'refunded' : 'partially_refunded',
            ]);

            $refund->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Generate refund receipt
            $this->receiptService->generateRefundReceipt($refund);

            event(new PosRefundCompleted($refund, auth('admin')->user()));

            return $refund;
        });
    }

    public function rejectRefund(PosRefund $refund, string $reason): PosRefund
    {
        $refund->update(['status' => 'rejected', 'reason' => $reason]);
        event(new PosRefundRejected($refund, auth('admin')->user(), $reason));

        return $refund;
    }

    protected function generateRefundNumber(PosOrder $order): string
    {
        $seq = PosRefund::where('pos_order_id', $order->id)->count() + 1;

        return str_replace('POS-', 'RFN-', $order->order_number)."-{$seq}";
    }
}
