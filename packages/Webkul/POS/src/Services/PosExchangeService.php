<?php

namespace Webkul\POS\Services;

use Illuminate\Support\Facades\DB;
use Webkul\POS\Events\PosExchangeCreated;
use Webkul\POS\Models\PosExchange;
use Webkul\POS\Models\PosOrder;
use Webkul\POS\Models\PosSession;
use Webkul\POS\Repositories\PosExchangeRepository;

class PosExchangeService
{
    public function __construct(
        protected PosExchangeRepository $exchangeRepository,
        protected PosCheckoutService $checkoutService,
        protected PosRefundService $refundService,
        protected PosPaymentService $paymentService,
    ) {}

    public function createExchange(PosSession $session, PosOrder $originalOrder, array $returnItems, array $newItems): PosExchange
    {
        return DB::transaction(function () use ($session, $originalOrder, $returnItems, $newItems) {
            // Create refund for returned items
            $refund = $this->refundService->initiateRefund(
                $originalOrder,
                $returnItems,
                'store_credit',
                'Exchange - items returned'
            );
            $this->refundService->completeRefund($refund);

            // Create new order for replacement items
            $newOrder = $this->checkoutService->createOrder($session, $newItems, [
                'customer_id' => $originalOrder->customer_id,
                'currency' => $originalOrder->currency,
            ]);

            // Calculate price difference
            $returnTotal = $refund->total_amount;
            $newTotal = $newOrder->total;
            $priceDifference = $newTotal - $returnTotal;

            $exchangeNumber = $this->generateExchangeNumber($originalOrder);

            $exchange = $this->exchangeRepository->create([
                'original_order_id' => $originalOrder->id,
                'new_order_id' => $newOrder->id,
                'pos_session_id' => $session->id,
                'admin_user_id' => auth('admin')->id(),
                'exchange_number' => $exchangeNumber,
                'price_difference' => $priceDifference,
                'status' => 'pending',
            ]);

            // If customer owes more, create a payment for the difference
            if ($priceDifference > 0) {
                $this->paymentService->processPayment($newOrder, 'cash', $priceDifference);
            }

            // Update original order status
            $originalItems = $originalOrder->items;
            $allExchanged = $originalItems->every(fn ($i) => $i->is_refunded);
            if ($allExchanged) {
                $originalOrder->update(['status' => 'exchanged']);
            }

            $exchange->update(['status' => 'completed']);

            event(new PosExchangeCreated($exchange, $originalOrder, $newOrder));

            return $exchange;
        });
    }

    protected function generateExchangeNumber(PosOrder $order): string
    {
        $seq = PosExchange::where('original_order_id', $order->id)->count() + 1;

        return str_replace('POS-', 'EXC-', $order->order_number)."-{$seq}";
    }
}
