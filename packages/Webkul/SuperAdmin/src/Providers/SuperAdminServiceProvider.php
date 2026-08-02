<?php

namespace Webkul\SuperAdmin\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\SuperAdmin\Http\Controllers\SuperAdminController;

class SuperAdminServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(dirname(__DIR__).'/Resources/views', 'super_admin');

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        Route::group(['prefix' => 'super-admin', 'middleware' => ['web', 'admin']], function () {
            Route::get('/', [SuperAdminController::class, 'index'])
                ->name('super_admin.dashboard');
            Route::get('create', [SuperAdminController::class, 'create'])
                ->name('super_admin.create');
            Route::post('create', [SuperAdminController::class, 'store'])
                ->name('super_admin.store');
            Route::get('impersonate/{tenantId}', [SuperAdminController::class, 'impersonate'])
                ->name('super_admin.impersonate');
        });
    }
}
