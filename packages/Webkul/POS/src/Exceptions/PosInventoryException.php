<?php

namespace Webkul\POS\Exceptions;

use Exception;

class PosInventoryException extends Exception
{
    public static function insufficientStock(int $productId, float $requested, float $available): self
    {
        return new self(
            "Insufficient stock for product {$productId}. Requested: {$requested}, Available: {$available}."
        );
    }

    public static function alreadyReserved(int $productId): self
    {
        return new self("Product {$productId} already has an active reservation.");
    }

    public static function reservationExpired(int $reservationId): self
    {
        return new self("Reservation {$reservationId} has expired.");
    }
}
