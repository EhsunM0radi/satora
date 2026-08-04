<?php

namespace Webkul\POS\Services;

use Webkul\POS\Events\PosReceiptPrinted;
use Webkul\POS\Jobs\SendPosReceipt;
use Webkul\POS\Models\PosOrder;
use Webkul\POS\Models\PosReceipt;
use Webkul\POS\Models\PosRefund;
use Webkul\POS\Repositories\PosReceiptRepository;

class PosReceiptService
{
    public function __construct(
        protected PosReceiptRepository $receiptRepository,
    ) {}

    public function generateSaleReceipt(PosOrder $order, string $deliveryMethod = 'print'): PosReceipt
    {
        $receiptNumber = $this->generateReceiptNumber('SALE');
        $content = $this->renderSaleReceiptHtml($order);

        $receipt = $this->receiptRepository->create([
            'pos_order_id' => $order->id,
            'receipt_number' => $receiptNumber,
            'type' => 'sale',
            'template' => 'thermal',
            'delivery_method' => $deliveryMethod,
            'recipient_email' => $order->customer?->email,
            'recipient_phone' => $order->customer?->phone,
            'content_html' => $content,
            'qr_code_data' => $this->generateVerificationQr($order),
        ]);

        if ($deliveryMethod === 'print') {
            $this->markPrinted($receipt);
        }

        if ($deliveryMethod === 'email' && $order->customer?->email) {
            dispatch(new SendPosReceipt($receipt));
        }

        return $receipt;
    }

    public function generateRefundReceipt(PosRefund $refund): PosReceipt
    {
        $receiptNumber = $this->generateReceiptNumber('RFND');
        $content = $this->renderRefundReceiptHtml($refund);

        $receipt = $this->receiptRepository->create([
            'pos_refund_id' => $refund->id,
            'receipt_number' => $receiptNumber,
            'type' => 'refund',
            'template' => 'thermal',
            'delivery_method' => 'print',
            'recipient_email' => $refund->order->customer?->email,
            'recipient_phone' => $refund->order->customer?->phone,
            'content_html' => $content,
        ]);

        $this->markPrinted($receipt);

        return $receipt;
    }

    public function markPrinted(PosReceipt $receipt): void
    {
        $receipt->update([
            'printed' => true,
            'printed_at' => now(),
        ]);

        event(new PosReceiptPrinted($receipt, $receipt->order?->terminal));
    }

    protected function renderSaleReceiptHtml(PosOrder $order): string
    {
        $lines = [];
        $lines[] = str_repeat('=', 48);
        $lines[] = str_pad(config('app.name', 'Satora'), 48, ' ', STR_PAD_BOTH);
        $lines[] = str_repeat('-', 48);
        $lines[] = 'Order: '.$order->order_number;
        $lines[] = 'Date:  '.$order->created_at->format('Y-m-d H:i');
        $lines[] = 'Cashier: '.($order->cashier->name ?? 'N/A');
        $lines[] = str_repeat('-', 48);

        foreach ($order->items as $item) {
            $lines[] = $item->name;
            $lines[] = sprintf('  %s x %s  %s',
                number_format($item->quantity, 0),
                number_format($item->unit_price),
                number_format($item->total)
            );
        }

        $lines[] = str_repeat('-', 48);
        $lines[] = sprintf('Subtotal:  %s', number_format($order->subtotal));
        if ($order->discount_amount > 0) {
            $lines[] = sprintf('Discount: -%s', number_format($order->discount_amount));
        }
        if ($order->tax_amount > 0) {
            $lines[] = sprintf('Tax:       %s', number_format($order->tax_amount));
        }
        $lines[] = sprintf('TOTAL:     %s', number_format($order->total));

        foreach ($order->payments as $payment) {
            $lines[] = sprintf('%s: %s', ucfirst($payment->payment_method_code), number_format($payment->amount));
        }

        $lines[] = str_repeat('=', 48);
        $lines[] = config('pos.receipt.footer_text', 'Thank you!');
        $lines[] = '';

        return implode("\n", $lines);
    }

    protected function renderRefundReceiptHtml(PosRefund $refund): string
    {
        $lines = [];
        $lines[] = str_repeat('=', 48);
        $lines[] = str_pad('REFUND RECEIPT', 48, ' ', STR_PAD_BOTH);
        $lines[] = str_repeat('-', 48);
        $lines[] = 'Refund #: '.$refund->refund_number;
        $lines[] = 'Original Order: '.$refund->order->order_number;
        $lines[] = 'Date: '.now()->format('Y-m-d H:i');

        foreach ($refund->items as $item) {
            $lines[] = sprintf('%s x%s  %s',
                $item->orderItem->name,
                number_format($item->quantity, 0),
                number_format($item->amount)
            );
        }

        $lines[] = str_repeat('-', 48);
        $lines[] = sprintf('REFUND TOTAL: %s', number_format($refund->total_amount));
        $lines[] = str_repeat('=', 48);
        $lines[] = '';

        return implode("\n", $lines);
    }

    protected function generateReceiptNumber(string $prefix): string
    {
        return sprintf('%s-%s-%04d', $prefix, now()->format('Ymd'), PosReceipt::whereDate('created_at', today())->count() + 1);
    }

    protected function generateVerificationQr(PosOrder $order): string
    {
        return json_encode([
            'order' => $order->order_number,
            'total' => $order->total,
            'date' => $order->created_at->toIso8601String(),
            'hash' => hash('sha256', $order->order_number.$order->total.$order->created_at),
        ]);
    }
}
