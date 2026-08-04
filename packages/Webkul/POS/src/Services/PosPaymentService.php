<?php

namespace Webkul\POS\Services;

use Illuminate\Support\Facades\DB;
use Webkul\POS\Contracts\PosPaymentProvider;
use Webkul\POS\Events\PosPaymentFailed;
use Webkul\POS\Events\PosPaymentReceived;
use Webkul\POS\Events\PosPaymentRefunded;
use Webkul\POS\Exceptions\PosPaymentException;
use Webkul\POS\Models\PosOrder;
use Webkul\POS\Models\PosPayment;
use Webkul\POS\Repositories\PosPaymentRepository;

class PosPaymentService
{
    protected array $providers = [];

    public function __construct(
        protected PosPaymentRepository $paymentRepository,
        protected PosCashRegisterService $cashRegisterService,
    ) {}

    public function getProvider(string $code): PosPaymentProvider
    {
        if (isset($this->providers[$code])) {
            return $this->providers[$code];
        }

        $providers = config('pos.payment.providers', []);
        if (! isset($providers[$code])) {
            throw PosPaymentException::providerNotFound($code);
        }

        return $this->providers[$code] = app($providers[$code]);
    }

    public function processPayment(PosOrder $order, string $paymentMethodCode, float $amount, array $extra = []): PosPayment
    {
        $provider = $this->getProvider($paymentMethodCode);

        $payment = DB::transaction(function () use ($order, $provider, $amount, $paymentMethodCode, $extra) {
            $result = $provider->process(array_merge($extra, ['amount' => $amount]));

            if (! $result->success) {
                $errorMessage = $result->message ?? 'Payment declined';
                $payment = $this->paymentRepository->create([
                    'pos_order_id' => $order->id,
                    'payment_method_code' => $paymentMethodCode,
                    'amount' => $amount,
                    'status' => 'declined',
                    'gateway_response' => $result->gatewayResponse,
                ]);

                event(new PosPaymentFailed($payment, $order, $errorMessage));
                throw new PosPaymentException($errorMessage);
            }

            $payment = $this->paymentRepository->create([
                'pos_order_id' => $order->id,
                'pos_cash_register_id' => $extra['cash_register_id'] ?? null,
                'payment_method_code' => $paymentMethodCode,
                'amount' => $amount,
                'reference_number' => $result->referenceNumber,
                'status' => 'approved',
                'gateway_response' => $result->gatewayResponse,
                'paid_at' => now(),
            ]);

            // Update order paid amount
            $newPaid = $order->paid_amount + $amount;
            $order->update([
                'paid_amount' => $newPaid,
                'due_amount' => max(0, $order->total - $newPaid),
            ]);

            // Update cash register if cash payment
            if ($paymentMethodCode === 'cash' && $payment->pos_cash_register_id) {
                $this->cashRegisterService->recordSale($payment->pos_cash_register_id, $amount, $payment);
            }

            event(new PosPaymentReceived($payment, $order));

            return $payment;
        });

        return $payment;
    }

    public function processSplitPayment(PosOrder $order, array $payments): array
    {
        $totalPaid = array_sum(array_column($payments, 'amount'));
        if ($totalPaid < $order->total) {
            throw PosPaymentException::insufficientPayment($order->total, $totalPaid);
        }

        $results = [];
        foreach ($payments as $paymentData) {
            $results[] = $this->processPayment(
                $order,
                $paymentData['method'],
                $paymentData['amount'],
                $paymentData['extra'] ?? []
            );
        }

        if ($order->paid_amount >= $order->total) {
            app(PosCheckoutService::class)->completeOrder($order);
        }

        return $results;
    }

    public function refundPayment(PosPayment $payment, float $amount): PosPayment
    {
        if ($payment->status === 'refunded') {
            throw PosPaymentException::alreadyRefunded($payment->id);
        }

        $provider = $this->getProvider($payment->payment_method_code);
        $result = $provider->refund($payment->reference_number, $amount);

        $payment->update([
            'status' => 'refunded',
            'gateway_response' => array_merge(
                $payment->gateway_response ?? [],
                ['refund' => $result->gatewayResponse]
            ),
        ]);

        event(new PosPaymentRefunded($payment, null));

        return $payment;
    }

    public function getAvailableMethods(): array
    {
        $methods = [];
        $providers = config('pos.payment.providers', []);

        foreach ($providers as $code => $class) {
            $provider = $this->getProvider($code);
            $methods[] = [
                'code' => $code,
                'name' => $provider->getName(),
            ];
        }

        return $methods;
    }
}
