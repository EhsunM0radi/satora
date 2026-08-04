<?php

namespace Webkul\POS\Services;

use Webkul\POS\Events\PosBarcodeScanned;
use Webkul\POS\Models\PosHardwareEvent;
use Webkul\POS\Models\PosTerminal;
use Webkul\POS\Repositories\PosHardwareEventRepository;

class PosHardwareService
{
    public function __construct(
        protected PosHardwareEventRepository $hardwareEventRepository,
    ) {}

    public function handleBarcodeScan(PosTerminal $terminal, string $barcode): array
    {
        $product = null;

        // Search for product by barcode
        $product = \DB::table('product_flat')
            ->where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->first();

        // Log hardware event
        $this->hardwareEventRepository->create([
            'pos_terminal_id' => $terminal->id,
            'device_type' => 'barcode_scanner',
            'event_type' => 'scan',
            'payload' => ['barcode' => $barcode, 'found' => (bool) $product],
        ]);

        if ($product) {
            event(new PosBarcodeScanned($terminal, $barcode, $product));
        }

        return [
            'barcode' => $barcode,
            'product' => $product,
            'found' => (bool) $product,
        ];
    }

    public function logPrinterEvent(PosTerminal $terminal, string $eventType, array $data = []): PosHardwareEvent
    {
        return $this->hardwareEventRepository->create([
            'pos_terminal_id' => $terminal->id,
            'device_type' => 'receipt_printer',
            'event_type' => $eventType,
            'payload' => $data,
        ]);
    }

    public function logDrawerEvent(PosTerminal $terminal, string $eventType, array $data = []): PosHardwareEvent
    {
        return $this->hardwareEventRepository->create([
            'pos_terminal_id' => $terminal->id,
            'device_type' => 'cash_drawer',
            'event_type' => $eventType,
            'payload' => $data,
        ]);
    }

    public function logScaleEvent(PosTerminal $terminal, float $weight, string $unit = 'kg'): PosHardwareEvent
    {
        return $this->hardwareEventRepository->create([
            'pos_terminal_id' => $terminal->id,
            'device_type' => 'weight_scale',
            'event_type' => 'weigh',
            'payload' => ['weight' => $weight, 'unit' => $unit],
        ]);
    }

    public function getHardwareProfile(PosTerminal $terminal): array
    {
        return $terminal->hardware_profile ?? [
            'barcode_scanner' => ['driver' => 'keyboard_wedge'],
            'receipt_printer' => ['driver' => 'generic_thermal', 'chars_per_line' => 48],
            'cash_drawer' => ['driver' => 'receipt_printer_kick'],
            'customer_display' => ['driver' => 'none'],
            'weight_scale' => ['driver' => 'none'],
        ];
    }
}
