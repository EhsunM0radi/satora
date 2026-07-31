<?php

namespace Webkul\Tenant\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Tenant\Http\Controllers\TenantController;
use Webkul\Tenant\Http\Middleware\ResolveTenant;
use Webkul\Tenant\Repositories\TenantRepository;
use Webkul\Tenant\TenantResolver;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/tenant.php', 'tenant'
        );

        $this->app->singleton(TenantResolver::class, function ($app) {
            return new TenantResolver($app->make(TenantRepository::class));
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/Database/Migrations');
        $this->loadTranslationsFrom(dirname(__DIR__).'/Resources/lang', 'tenant');
        $this->loadViewsFrom(dirname(__DIR__).'/Resources/views', 'tenant');

        $this->registerRoutes();
        $this->registerMiddleware();
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'api/v1',
            'middleware' => 'api',
        ], function () {
            Route::post('tenant', [TenantController::class, 'store'])
                ->name('api.tenant.store');
        });
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('tenant', ResolveTenant::class);
    }
}
