<?php

namespace Webkul\POS\Services\PaymentProviders;

use Webkul\POS\Contracts\PosPaymentProvider;
use Webkul\POS\Contracts\PosPaymentResult;

class WalletProvider implements PosPaymentProvider
{
    public function getName(): string
    {
        return 'Wallet';
    }

    public function getCode(): string
    {
        return 'wallet';
    }

    public function process(array $data): PosPaymentResult
    {
        $customerId = $data['customer_id'] ?? null;
        if (! $customerId) {
            return PosPaymentResult::failure('Customer ID required for wallet payment.');
        }

        // TODO: Integrate with actual wallet/credit system
        $transactionId = 'WALL-'.uniqid();

        return PosPaymentResult::success(
            transactionId: $transactionId,
            referenceNumber: 'WALLET-'.$customerId,
            gatewayResponse: ['method' => 'wallet', 'amount' => $data['amount'], 'customer_id' => $customerId]
        );
    }

    public function refund(string $transactionId, float $amount): PosPaymentResult
    {
        return PosPaymentResult::success(
            transactionId: 'RFND-'.uniqid(),
            referenceNumber: $transactionId,
            gatewayResponse: ['method' => 'wallet', 'refund_amount' => $amount]
        );
    }
}
