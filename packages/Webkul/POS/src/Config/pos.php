<?php

use Webkul\POS\Services\PaymentProviders\CardProvider;
use Webkul\POS\Services\PaymentProviders\CashProvider;
use Webkul\POS\Services\PaymentProviders\GiftCardProvider;
use Webkul\POS\Services\PaymentProviders\InstallmentProvider;
use Webkul\POS\Services\PaymentProviders\StoreCreditProvider;
use Webkul\POS\Services\PaymentProviders\WalletProvider;

return [
    'stock_reservation_ttl' => 15, // minutes
    'default_currency' => 'IRR',
    'receipt' => [
        'default_template' => 'thermal',
        'show_logo' => true,
        'show_barcode' => true,
        'show_qr_code' => true,
        'footer_text' => 'Thank you for your purchase!',
    ],
    'offline' => [
        'max_queue_size' => 1000,
        'sync_interval_seconds' => 30,
        'product_cache_ttl' => 3600,
        'customer_cache_ttl' => 86400,
    ],
    'hardware' => [
        'barcode_scanner' => [
            'driver' => 'keyboard_wedge',
            'prefix' => null,
            'suffix' => '\n',
        ],
        'receipt_printer' => [
            'driver' => 'generic_thermal',
            'chars_per_line' => 48,
            'baud_rate' => 9600,
        ],
        'cash_drawer' => [
            'driver' => 'receipt_printer_kick',
        ],
        'customer_display' => [
            'driver' => 'none',
        ],
        'weight_scale' => [
            'driver' => 'none',
        ],
    ],
    'payment' => [
        'providers' => [
            'cash' => CashProvider::class,
            'card' => CardProvider::class,
            'wallet' => WalletProvider::class,
            'gift_card' => GiftCardProvider::class,
            'store_credit' => StoreCreditProvider::class,
            'installment' => InstallmentProvider::class,
        ],
    ],
    'ai' => [
        'data_collection_enabled' => true,
        'prediction_horizon_days' => 30,
        'recommendation_min_confidence' => 0.7,
        'fraud_detection_enabled' => true,
    ],
    'marketplace' => [
        'default_commission_rate' => 10.0, // percentage
        'commission_calculation' => 'order_total', // order_total | item_subtotal
    ],
    'audit' => [
        'enabled' => true,
        'retention_days' => 365,
    ],
];
