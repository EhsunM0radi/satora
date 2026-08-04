<?php

namespace Webkul\POS\Services\PaymentProviders;

use Webkul\POS\Contracts\PosPaymentProvider;
use Webkul\POS\Contracts\PosPaymentResult;

class StoreCreditProvider implements PosPaymentProvider
{
    public function getName(): string
    {
        return 'Store Credit';
    }

    public function getCode(): string
    {
        return 'store_credit';
    }

    public function process(array $data): PosPaymentResult
    {
        $customerId = $data['customer_id'] ?? null;
        if (! $customerId) {
            return PosPaymentResult::failure('Customer ID required for store credit payment.');
        }

        $transactionId = 'CRED-'.uniqid();

        return PosPaymentResult::success(
            transactionId: $transactionId,
            referenceNumber: 'CREDIT-'.$customerId,
            gatewayResponse: ['method' => 'store_credit', 'amount' => $data['amount'], 'customer_id' => $customerId]
        );
    }

    public function refund(string $transactionId, float $amount): PosPaymentResult
    {
        return PosPaymentResult::success(
            transactionId: 'RFND-'.uniqid(),
            referenceNumber: $transactionId,
            gatewayResponse: ['method' => 'store_credit', 'refund_amount' => $amount]
        );
    }
}
