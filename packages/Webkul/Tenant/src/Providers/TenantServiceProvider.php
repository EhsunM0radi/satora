<?php

namespace Webkul\Tenant\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Webkul\Tenant\Http\Middleware\ResolveTenant;
use Webkul\Tenant\TenantResolver;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/tenant.php', 'tenant'
        );

        $this->app->singleton(TenantResolver::class, function ($app) {
            return new TenantResolver($app->make(\Webkul\Tenant\Repositories\TenantRepository::class));
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
        // API
        Route::group(['prefix' => 'api/v1', 'middleware' => 'api'], function () {
            Route::post('tenant', [\Webkul\Tenant\Http\Controllers\TenantController::class, 'store'])
                ->name('api.tenant.store');
        });

        // Web
        Route::group(['middleware' => 'web'], function () {
            // Signup
            Route::get('signup', [\Webkul\Tenant\Http\Controllers\SignupController::class, 'show'])
                ->name('signup.show');
            Route::post('signup', [\Webkul\Tenant\Http\Controllers\SignupController::class, 'store'])
                ->name('signup.store');

            // Onboarding wizard (requires admin auth)
            Route::middleware('admin')->group(function () {
                Route::get('onboarding/{step?}', [\Webkul\Tenant\Http\Controllers\OnboardingController::class, 'show'])
                    ->name('onboarding.show');
                Route::post('onboarding', [\Webkul\Tenant\Http\Controllers\OnboardingController::class, 'store'])
                    ->name('onboarding.store');
            });
        });
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('tenant', ResolveTenant::class);
    }
}
