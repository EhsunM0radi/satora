<?php

use Illuminate\Support\Facades\Route;
use Webkul\POS\Http\Controllers\Api\PosCashController as ApiPosCashController;
use Webkul\POS\Http\Controllers\Api\PosCustomerController as ApiPosCustomerController;
use Webkul\POS\Http\Controllers\Api\PosExchangeController as ApiPosExchangeController;
use Webkul\POS\Http\Controllers\Api\PosOrderController as ApiPosOrderController;
use Webkul\POS\Http\Controllers\Api\PosPaymentController as ApiPosPaymentController;
use Webkul\POS\Http\Controllers\Api\PosProductController as ApiPosProductController;
use Webkul\POS\Http\Controllers\Api\PosRefundController as ApiPosRefundController;
use Webkul\POS\Http\Controllers\Api\PosSessionController as ApiPosSessionController;
use Webkul\POS\Http\Controllers\Api\PosSyncController as ApiPosSyncController;

Route::group([
    'middleware' => ['api', 'auth:admin'],
], function () {
    // Sessions
    Route::post('sessions/open', [ApiPosSessionController::class, 'open']);
    Route::post('sessions/close', [ApiPosSessionController::class, 'close']);
    Route::get('sessions/current', [ApiPosSessionController::class, 'current']);
    Route::get('sessions/{id}', [ApiPosSessionController::class, 'show']);

    // Orders
    Route::get('orders', [ApiPosOrderController::class, 'index']);
    Route::post('orders', [ApiPosOrderController::class, 'store']);
    Route::get('orders/{id}', [ApiPosOrderController::class, 'show']);
    Route::put('orders/{id}', [ApiPosOrderController::class, 'update']);
    Route::post('orders/{id}/hold', [ApiPosOrderController::class, 'hold']);
    Route::post('orders/{id}/resume', [ApiPosOrderController::class, 'resume']);
    Route::post('orders/{id}/void', [ApiPosOrderController::class, 'void']);
    Route::post('orders/{id}/complete', [ApiPosOrderController::class, 'complete']);

    // Products
    Route::get('products/search', [ApiPosProductController::class, 'search']);
    Route::get('products/barcode/{barcode}', [ApiPosProductController::class, 'byBarcode']);
    Route::get('products/categories', [ApiPosProductController::class, 'categories']);
    Route::get('products/favorites', [ApiPosProductController::class, 'favorites']);
    Route::get('products/recent', [ApiPosProductController::class, 'recent']);

    // Customers
    Route::get('customers/search', [ApiPosCustomerController::class, 'search']);
    Route::post('customers', [ApiPosCustomerController::class, 'store']);
    Route::get('customers/{id}', [ApiPosCustomerController::class, 'show']);
    Route::get('customers/{id}/purchases', [ApiPosCustomerController::class, 'purchases']);

    // Payments
    Route::post('payments/process', [ApiPosPaymentController::class, 'process']);
    Route::post('payments/split', [ApiPosPaymentController::class, 'split']);

    // Refunds
    Route::post('refunds', [ApiPosRefundController::class, 'store']);
    Route::get('refunds/{id}', [ApiPosRefundController::class, 'show']);

    // Exchanges
    Route::post('exchanges', [ApiPosExchangeController::class, 'store']);
    Route::get('exchanges/{id}', [ApiPosExchangeController::class, 'show']);

    // Cash Management
    Route::post('cash/open-balance', [ApiPosCashController::class, 'setOpeningBalance']);
    Route::post('cash/movement', [ApiPosCashController::class, 'createMovement']);
    Route::get('cash/register', [ApiPosCashController::class, 'currentRegister']);

    // Sync (Offline)
    Route::post('sync/push', [ApiPosSyncController::class, 'push']);
    Route::get('sync/pull', [ApiPosSyncController::class, 'pull']);
    Route::get('sync/status', [ApiPosSyncController::class, 'status']);
});
