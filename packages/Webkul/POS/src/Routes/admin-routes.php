<?php

use Illuminate\Support\Facades\Route;
use Webkul\POS\Http\Controllers\Admin\PosDashboardController;
use Webkul\POS\Http\Controllers\Admin\PosDiscountController;
use Webkul\POS\Http\Controllers\Admin\PosEmployeeController;
use Webkul\POS\Http\Controllers\Admin\PosLocationController;
use Webkul\POS\Http\Controllers\Admin\PosOrderController;
use Webkul\POS\Http\Controllers\Admin\PosRefundController;
use Webkul\POS\Http\Controllers\Admin\PosReportController;
use Webkul\POS\Http\Controllers\Admin\PosSessionController;
use Webkul\POS\Http\Controllers\Admin\PosSettingsController;
use Webkul\POS\Http\Controllers\Admin\PosTerminalController;

Route::group([
    'middleware' => ['web', 'admin'],
], function () {
    Route::prefix('pos')->group(function () {
        // Dashboard
        Route::get('dashboard', [PosDashboardController::class, 'index'])
            ->name('admin.pos.dashboard');

        // Locations
        Route::resource('locations', PosLocationController::class)
            ->names('admin.pos.locations');

        // Terminals
        Route::resource('terminals', PosTerminalController::class)
            ->names('admin.pos.terminals');

        // Sessions
        Route::get('sessions', [PosSessionController::class, 'index'])
            ->name('admin.pos.sessions.index');
        Route::get('sessions/{id}', [PosSessionController::class, 'show'])
            ->name('admin.pos.sessions.show');

        // Orders
        Route::get('orders', [PosOrderController::class, 'index'])
            ->name('admin.pos.orders.index');
        Route::get('orders/{id}', [PosOrderController::class, 'show'])
            ->name('admin.pos.orders.show');
        Route::post('orders/{id}/void', [PosOrderController::class, 'void'])
            ->name('admin.pos.orders.void');

        // Refunds
        Route::get('refunds', [PosRefundController::class, 'index'])
            ->name('admin.pos.refunds.index');
        Route::get('refunds/{id}', [PosRefundController::class, 'show'])
            ->name('admin.pos.refunds.show');

        // Employees
        Route::resource('employees', PosEmployeeController::class)
            ->names('admin.pos.employees');

        // Discounts
        Route::resource('discounts', PosDiscountController::class)
            ->names('admin.pos.discounts');

        // Settings
        Route::get('settings', [PosSettingsController::class, 'index'])
            ->name('admin.pos.settings.index');
        Route::post('settings', [PosSettingsController::class, 'store'])
            ->name('admin.pos.settings.store');

        // Reports
        Route::get('reports/sales', [PosReportController::class, 'sales'])
            ->name('admin.pos.reports.sales');
        Route::get('reports/cashier', [PosReportController::class, 'cashier'])
            ->name('admin.pos.reports.cashier');
        Route::get('reports/products', [PosReportController::class, 'products'])
            ->name('admin.pos.reports.products');
        Route::get('reports/payments', [PosReportController::class, 'payments'])
            ->name('admin.pos.reports.payments');
        Route::get('reports/inventory', [PosReportController::class, 'inventory'])
            ->name('admin.pos.reports.inventory');
    });
});
