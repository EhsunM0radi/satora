<?php

namespace Webkul\POS\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\POS\Services\PosAIDataService;
use Webkul\POS\Services\PosCashRegisterService;
use Webkul\POS\Services\PosCheckoutService;
use Webkul\POS\Services\PosDiscountService;
use Webkul\POS\Services\PosExchangeService;
use Webkul\POS\Services\PosHardwareService;
use Webkul\POS\Services\PosInventoryService;
use Webkul\POS\Services\PosMarketplaceService;
use Webkul\POS\Services\PosOfflineSyncService;
use Webkul\POS\Services\PosPaymentService;
use Webkul\POS\Services\PosReceiptService;
use Webkul\POS\Services\PosRefundService;
use Webkul\POS\Services\PosReportingService;
use Webkul\POS\Services\PosSessionService;

class POSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/pos.php', 'pos');
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/admin-menu.php', 'menu');
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/acl.php', 'acl');
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/system.php', 'system');

        $this->registerServices();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/Database/Migrations');
        $this->loadTranslationsFrom(dirname(__DIR__).'/Resources/lang', 'pos');
        $this->loadViewsFrom(dirname(__DIR__).'/Resources/views', 'pos');

        $this->registerRoutes();
        $this->registerPolicies();
    }

    protected function registerServices(): void
    {
        $services = [
            'pos.session' => PosSessionService::class,
            'pos.checkout' => PosCheckoutService::class,
            'pos.payment' => PosPaymentService::class,
            'pos.refund' => PosRefundService::class,
            'pos.exchange' => PosExchangeService::class,
            'pos.inventory' => PosInventoryService::class,
            'pos.receipt' => PosReceiptService::class,
            'pos.hardware' => PosHardwareService::class,
            'pos.offline_sync' => PosOfflineSyncService::class,
            'pos.discount' => PosDiscountService::class,
            'pos.cash_register' => PosCashRegisterService::class,
            'pos.reporting' => PosReportingService::class,
            'pos.marketplace' => PosMarketplaceService::class,
            'pos.ai_data' => PosAIDataService::class,
        ];

        foreach ($services as $key => $class) {
            $this->app->singleton($key, fn ($app) => $app->make($class));
        }
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('app.admin_url'),
            'middleware' => ['web', 'admin'],
        ], function () {
            $this->loadRoutesFrom(dirname(__DIR__).'/Routes/admin-routes.php');
        });

        Route::group([
            'prefix' => 'api/pos',
            'middleware' => ['api', 'auth:admin'],
        ], function () {
            $this->loadRoutesFrom(dirname(__DIR__).'/Routes/api-routes.php');
        });
    }

    protected function registerPolicies(): void
    {
        Gate::define('pos.create_sale', fn ($user) => $user->can('pos.create_sale'));
        Gate::define('pos.cancel_sale', fn ($user) => $user->can('pos.cancel_sale'));
        Gate::define('pos.process_refund', fn ($user) => $user->can('pos.process_refund'));
        Gate::define('pos.apply_discount', fn ($user) => $user->can('pos.apply_discount'));
        Gate::define('pos.change_price', fn ($user) => $user->can('pos.change_price'));
        Gate::define('pos.open_drawer', fn ($user) => $user->can('pos.open_drawer'));
        Gate::define('pos.view_reports', fn ($user) => $user->can('pos.view_reports'));
        Gate::define('pos.edit_products', fn ($user) => $user->can('pos.edit_products'));
        Gate::define('pos.access_customers', fn ($user) => $user->can('pos.access_customers'));
        Gate::define('pos.manage_sessions', fn ($user) => $user->can('pos.manage_sessions'));
        Gate::define('pos.manage_employees', fn ($user) => $user->can('pos.manage_employees'));
        Gate::define('pos.view_analytics', fn ($user) => $user->can('pos.view_analytics'));
    }
}
