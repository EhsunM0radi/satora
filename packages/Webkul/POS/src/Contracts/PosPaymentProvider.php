<?php

namespace Webkul\POS\Contracts;

interface PosPaymentProvider
{
    public function getName(): string;

    public function getCode(): string;

    public function process(array $data): PosPaymentResult;

    public function refund(string $transactionId, float $amount): PosPaymentResult;
}
