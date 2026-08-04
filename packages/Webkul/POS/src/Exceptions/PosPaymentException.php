<?php

namespace Webkul\POS\Exceptions;

use Exception;

class PosPaymentException extends Exception
{
    public static function insufficientPayment(float $total, float $paid): self
    {
        return new self("Insufficient payment. Total: {$total}, Paid: {$paid}.");
    }

    public static function alreadyRefunded(int $paymentId): self
    {
        return new self("Payment {$paymentId} has already been refunded.");
    }

    public static function providerNotFound(string $code): self
    {
        return new self("Payment provider '{$code}' not found. Check pos.payment.providers config.");
    }
}
