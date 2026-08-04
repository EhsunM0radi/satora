<?php

namespace Webkul\POS\Services\PaymentProviders;

use Webkul\POS\Contracts\PosPaymentProvider;
use Webkul\POS\Contracts\PosPaymentResult;

class CardProvider implements PosPaymentProvider
{
    public function getName(): string
    {
        return 'Card Terminal';
    }

    public function getCode(): string
    {
        return 'card';
    }

    public function process(array $data): PosPaymentResult
    {
        $transactionId = 'CARD-'.uniqid();

        return PosPaymentResult::success(
            transactionId: $transactionId,
            referenceNumber: $data['card_last_four'] ?? 'XXXX',
            gatewayResponse: [
                'method' => 'card',
                'amount' => $data['amount'],
                'card_type' => $data['card_type'] ?? 'unknown',
                'auth_code' => strtoupper(substr(md5(uniqid()), 0, 6)),
            ]
        );
    }

    public function refund(string $transactionId, float $amount): PosPaymentResult
    {
        return PosPaymentResult::success(
            transactionId: 'RFND-'.uniqid(),
            referenceNumber: $transactionId,
            gatewayResponse: ['method' => 'card', 'refund_amount' => $amount]
        );
    }
}
