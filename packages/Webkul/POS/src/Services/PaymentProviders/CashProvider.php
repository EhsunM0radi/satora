<?php

namespace Webkul\POS\Services\PaymentProviders;

use Webkul\POS\Contracts\PosPaymentProvider;
use Webkul\POS\Contracts\PosPaymentResult;

class CashProvider implements PosPaymentProvider
{
    public function getName(): string
    {
        return 'Cash';
    }

    public function getCode(): string
    {
        return 'cash';
    }

    public function process(array $data): PosPaymentResult
    {
        $transactionId = 'CASH-'.uniqid();

        return PosPaymentResult::success(
            transactionId: $transactionId,
            referenceNumber: 'MANUAL',
            gatewayResponse: ['method' => 'cash', 'amount' => $data['amount']]
        );
    }

    public function refund(string $transactionId, float $amount): PosPaymentResult
    {
        return PosPaymentResult::success(
            transactionId: 'RFND-'.uniqid(),
            referenceNumber: $transactionId,
            gatewayResponse: ['method' => 'cash', 'refund_amount' => $amount]
        );
    }
}
