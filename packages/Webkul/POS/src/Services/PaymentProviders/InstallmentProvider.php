<?php

namespace Webkul\POS\Services\PaymentProviders;

use Webkul\POS\Contracts\PosPaymentProvider;
use Webkul\POS\Contracts\PosPaymentResult;

class InstallmentProvider implements PosPaymentProvider
{
    public function getName(): string
    {
        return 'Installment';
    }

    public function getCode(): string
    {
        return 'installment';
    }

    public function process(array $data): PosPaymentResult
    {
        $installments = $data['installments'] ?? 1;
        $transactionId = 'INST-'.uniqid();

        return PosPaymentResult::success(
            transactionId: $transactionId,
            referenceNumber: 'INST-'.$installments.'X',
            gatewayResponse: [
                'method' => 'installment',
                'amount' => $data['amount'],
                'installments' => $installments,
                'per_installment' => round($data['amount'] / $installments, 2),
            ]
        );
    }

    public function refund(string $transactionId, float $amount): PosPaymentResult
    {
        return PosPaymentResult::success(
            transactionId: 'RFND-'.uniqid(),
            referenceNumber: $transactionId,
            gatewayResponse: ['method' => 'installment', 'refund_amount' => $amount]
        );
    }
}
