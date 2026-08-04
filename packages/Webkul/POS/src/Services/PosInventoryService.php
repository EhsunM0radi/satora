<?php

namespace Webkul\POS\Services;

use Carbon\Carbon;
use Webkul\POS\Events\PosLowStockAlert;
use Webkul\POS\Events\PosStockDeducted;
use Webkul\POS\Events\PosStockReleased;
use Webkul\POS\Events\PosStockReserved;
use Webkul\POS\Exceptions\PosInventoryException;
use Webkul\POS\Models\PosInventoryReservation;
use Webkul\POS\Repositories\PosInventoryReservationRepository;

class PosInventoryService
{
    public function __construct(
        protected PosInventoryReservationRepository $reservationRepository,
    ) {}

    public function reserveStock(int $productId, float $quantity, int $inventorySourceId, ?int $orderId = null, ?int $orderItemId = null): PosInventoryReservation
    {
        $available = $this->getAvailableStock($productId, $inventorySourceId);

        if ($quantity > $available) {
            throw PosInventoryException::insufficientStock($productId, $quantity, $available);
        }

        $ttl = config('pos.stock_reservation_ttl', 15);

        $reservation = $this->reservationRepository->create([
            'product_id' => $productId,
            'variant_id' => null,
            'inventory_source_id' => $inventorySourceId,
            'pos_order_id' => $orderId,
            'pos_order_item_id' => $orderItemId,
            'quantity' => $quantity,
            'status' => 'reserved',
            'expires_at' => Carbon::now()->addMinutes($ttl),
        ]);

        event(new PosStockReserved($reservation, null));

        // Check low stock threshold after reservation
        $remainingStock = $this->getAvailableStock($productId, $inventorySourceId);
        $threshold = $this->getLowStockThreshold($productId);

        if ($remainingStock <= $threshold) {
            event(new PosLowStockAlert(null, $remainingStock, $threshold));
        }

        return $reservation;
    }

    public function confirmReservation(PosInventoryReservation $reservation): void
    {
        $reservation->update(['status' => 'confirmed', 'expires_at' => null]);
        event(new PosStockDeducted($reservation->orderItem, null, $reservation->quantity));
    }

    public function releaseReservation(PosInventoryReservation $reservation): void
    {
        $reservation->update(['status' => 'released']);
        event(new PosStockReleased($reservation, null));
    }

    public function returnStock(int $productId, float $quantity, int $inventorySourceId): void
    {
        // Create a negative reservation (return) to track the restock
        $this->reservationRepository->create([
            'product_id' => $productId,
            'inventory_source_id' => $inventorySourceId,
            'quantity' => -$quantity,
            'status' => 'confirmed',
            'expires_at' => null,
        ]);
    }

    public function getAvailableStock(int $productId, int $inventorySourceId): float
    {
        $reserved = PosInventoryReservation::where('product_id', $productId)
            ->where('inventory_source_id', $inventorySourceId)
            ->where('status', 'reserved')
            ->sum('quantity');

        // Bagisto inventory integration: check inventory_sources table
        $inventoryQty = \DB::table('inventory_sources')
            ->where('id', $inventorySourceId)
            ->value('qty') ?? 0;

        return max(0, $inventoryQty - $reserved);
    }

    public function checkLowStock(int $productId, int $inventorySourceId): ?float
    {
        $available = $this->getAvailableStock($productId, $inventorySourceId);
        $threshold = $this->getLowStockThreshold($productId);

        if ($available <= $threshold) {
            event(new PosLowStockAlert(null, $available, $threshold));

            return $available;
        }

        return null;
    }

    public function releaseExpiredReservations(): int
    {
        $expired = PosInventoryReservation::where('status', 'reserved')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        foreach ($expired as $reservation) {
            $this->releaseReservation($reservation);
        }

        return $expired->count();
    }

    protected function getLowStockThreshold(int $productId): int
    {
        return \DB::table('products')
            ->where('id', $productId)
            ->value('min_qty') ?? 5;
    }
}
