<?php

namespace Webkul\POS\Contracts;

class PosPaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $transactionId = null,
        public readonly ?string $referenceNumber = null,
        public readonly ?string $message = null,
        public readonly array $gatewayResponse = [],
    ) {}

    public static function success(string $transactionId, ?string $referenceNumber = null, array $gatewayResponse = []): self
    {
        return new self(true, $transactionId, $referenceNumber, null, $gatewayResponse);
    }

    public static function failure(string $message, array $gatewayResponse = []): self
    {
        return new self(false, null, null, $message, $gatewayResponse);
    }
}
