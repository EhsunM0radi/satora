<?php

namespace Webkul\POS\Services\PaymentProviders;

use Webkul\POS\Contracts\PosPaymentProvider;
use Webkul\POS\Contracts\PosPaymentResult;

class GiftCardProvider implements PosPaymentProvider
{
    public function getName(): string
    {
        return 'Gift Card';
    }

    public function getCode(): string
    {
        return 'gift_card';
    }

    public function process(array $data): PosPaymentResult
    {
        $transactionId = 'GIFT-'.uniqid();

        return PosPaymentResult::success(
            transactionId: $transactionId,
            referenceNumber: $data['gift_card_number'] ?? 'XXXX',
            gatewayResponse: [
                'method' => 'gift_card',
                'amount' => $data['amount'],
                'gift_card_number' => $data['gift_card_number'] ?? null,
            ]
        );
    }

    public function refund(string $transactionId, float $amount): PosPaymentResult
    {
        return PosPaymentResult::success(
            transactionId: 'RFND-'.uniqid(),
            referenceNumber: $transactionId,
            gatewayResponse: ['method' => 'gift_card', 'refund_amount' => $amount]
        );
    }
}
