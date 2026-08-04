<?php

namespace Webkul\POS\Services;

use Illuminate\Support\Facades\DB;
use Webkul\Customer\Models\Customer;
use Webkul\POS\Events\PosCustomerAttached;
use Webkul\POS\Events\PosOrderCompleted;
use Webkul\POS\Events\PosOrderCreated;
use Webkul\POS\Events\PosOrderHeld;
use Webkul\POS\Events\PosOrderResumed;
use Webkul\POS\Events\PosOrderVoided;
use Webkul\POS\Exceptions\PosSessionException;
use Webkul\POS\Models\PosOrder;
use Webkul\POS\Models\PosOrderItem;
use Webkul\POS\Models\PosSession;
use Webkul\POS\Repositories\PosOrderItemRepository;
use Webkul\POS\Repositories\PosOrderRepository;

class PosCheckoutService
{
    public function __construct(
        protected PosOrderRepository $orderRepository,
        protected PosOrderItemRepository $orderItemRepository,
        protected PosDiscountService $discountService,
        protected PosTaxService $taxService,
    ) {}

    public function createOrder(PosSession $session, array $items, array $options = []): PosOrder
    {
        if (! $session->isOpen()) {
            throw PosSessionException::sessionClosed($session->id);
        }

        return DB::transaction(function () use ($session, $items, $options) {
            $subtotal = 0;

            $order = $this->orderRepository->create([
                'pos_session_id' => $session->id,
                'pos_terminal_id' => $session->pos_terminal_id,
                'customer_id' => $options['customer_id'] ?? null,
                'admin_user_id' => auth('admin')->id(),
                'seller_id' => $options['seller_id'] ?? null,
                'order_number' => $this->generateOrderNumber($session),
                'status' => 'draft',
                'currency' => $options['currency'] ?? config('pos.default_currency', 'IRR'),
                'tax_inclusive' => $options['tax_inclusive'] ?? false,
                'notes' => $options['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'] ?? $item['price'];

                $orderItem = $this->orderItemRepository->create([
                    'pos_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'inventory_source_id' => $item['inventory_source_id'] ?? null,
                    'name' => $item['name'],
                    'sku' => $item['sku'] ?? null,
                    'barcode' => $item['barcode'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $quantity,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'serial_number' => $item['serial_number'] ?? null,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'metadata' => $item['metadata'] ?? null,
                ]);

                $subtotal += $orderItem->total;
            }

            // Calculate totals
            $discountAmount = 0;
            if (! empty($options['discount_code'])) {
                $discountAmount = $this->discountService->apply($order, $options['discount_code']);
            }

            $taxAmount = $this->taxService->calculate($order, $subtotal - $discountAmount);
            $shippingAmount = $options['shipping_amount'] ?? 0;
            $total = $subtotal - $discountAmount + $taxAmount + $shippingAmount;

            $order->update([
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'total' => $total,
                'due_amount' => $total,
            ]);

            event(new PosOrderCreated($order, auth('admin')->user()));

            return $order->fresh();
        });
    }

    public function addItem(PosOrder $order, array $item): PosOrderItem
    {
        $qtOrderItem = $this->orderItemRepository->create([
            'pos_order_id' => $order->id,
            'product_id' => $item['product_id'],
            'variant_id' => $item['variant_id'] ?? null,
            'inventory_source_id' => $item['inventory_source_id'] ?? null,
            'name' => $item['name'],
            'sku' => $item['sku'] ?? null,
            'barcode' => $item['barcode'] ?? null,
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'] ?? $item['price'],
            'total' => ($item['unit_price'] ?? $item['price']) * $item['quantity'],
            'tax_rate' => $item['tax_rate'] ?? 0,
            'metadata' => $item['metadata'] ?? null,
        ]);

        $this->recalculateOrder($order);

        return $qtOrderItem;
    }

    public function removeItem(PosOrder $order, PosOrderItem $orderItem): void
    {
        $orderItem->delete();
        $this->recalculateOrder($order);
    }

    public function updateItemQuantity(PosOrder $order, PosOrderItem $orderItem, float $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($order, $orderItem);

            return;
        }

        $orderItem->update([
            'quantity' => $quantity,
            'total' => $orderItem->unit_price * $quantity,
        ]);

        $this->recalculateOrder($order);
    }

    public function attachCustomer(PosOrder $order, int $customerId): PosOrder
    {
        $order->update(['customer_id' => $customerId]);

        event(new PosCustomerAttached(
            $order,
            Customer::find($customerId),
            auth('admin')->user()
        ));

        return $order;
    }

    public function holdOrder(PosOrder $order): PosOrder
    {
        $order->update(['status' => 'held', 'held_at' => now()]);
        event(new PosOrderHeld($order, auth('admin')->user()));

        return $order;
    }

    public function resumeOrder(PosOrder $order): PosOrder
    {
        $order->update(['status' => 'draft', 'held_at' => null]);
        event(new PosOrderResumed($order, auth('admin')->user()));

        return $order;
    }

    public function voidOrder(PosOrder $order, string $reason): PosOrder
    {
        $order->update([
            'status' => 'voided',
            'voided_at' => now(),
            'notes' => trim(($order->notes ?? '')."\nVoided: {$reason}"),
        ]);

        event(new PosOrderVoided($order, auth('admin')->user(), $reason));

        return $order;
    }

    public function completeOrder(PosOrder $order): PosOrder
    {
        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
            'due_amount' => 0,
        ]);

        event(new PosOrderCompleted($order, auth('admin')->user()));

        return $order;
    }

    protected function recalculateOrder(PosOrder $order): void
    {
        $items = $order->items()->get();
        $subtotal = $items->sum('total');
        $taxAmount = $this->taxService->calculate($order, $subtotal - $order->discount_amount);
        $total = $subtotal - $order->discount_amount + $taxAmount + $order->shipping_amount;

        $order->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    protected function generateOrderNumber(PosSession $session): string
    {
        return sprintf(
            'POS-%s-%s-%04d',
            $session->terminal->code ?? 'XX',
            now()->format('Ymd'),
            PosOrder::where('pos_terminal_id', $session->pos_terminal_id)
                ->whereDate('created_at', today())
                ->count() + 1
        );
    }
}
