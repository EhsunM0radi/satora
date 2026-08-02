<?php

namespace Webkul\Tenant\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Shop\Http\Controllers\HomeController;
use Webkul\Shop\Http\Controllers\SearchController;
use Webkul\Tenant\Http\Controllers\OnboardingController;
use Webkul\Tenant\Http\Controllers\SignupController;
use Webkul\Tenant\Http\Controllers\SuperAdminController;
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
        Route::group(['prefix' => 'api/v1', 'middleware' => 'api'], function () {
            Route::post('tenant', [TenantController::class, 'store'])
                ->name('api.tenant.store');
        });

        Route::group(['middleware' => 'web'], function () {
            Route::get('signup', [SignupController::class, 'show'])
                ->name('signup.show');
            Route::post('signup', [SignupController::class, 'store'])
                ->name('signup.store');

            // OTP signup flow
            Route::post('signup/otp/send', [SignupController::class, 'sendOtp'])
                ->name('signup.otp.send');
            Route::get('signup/otp/verify', [SignupController::class, 'showOtpVerify'])
                ->name('signup.otp.verify');
            Route::post('signup/otp/verify', [SignupController::class, 'verifyOtp'])
                ->name('signup.otp.verify.submit');

            Route::middleware('admin')->group(function () {
                Route::get('onboarding/{step?}', [OnboardingController::class, 'show'])
                    ->name('onboarding.show');
                Route::post('onboarding', [OnboardingController::class, 'store'])
                    ->name('onboarding.store');
            });

            // Super Admin panel
            Route::prefix('super-admin')->middleware('admin')->name('super_admin.')->group(function () {
                Route::get('/', [SuperAdminController::class, 'index'])
                    ->name('dashboard');
                Route::get('create', [SuperAdminController::class, 'create'])
                    ->name('create');
                Route::post('create', [SuperAdminController::class, 'store'])
                    ->name('store');
                Route::get('impersonate/{tenantId}', [SuperAdminController::class, 'impersonate'])
                    ->name('impersonate');
            });

            // Local: path-based tenant storefront — satora.test/shop/{slug}
            if (app()->environment('local')) {
                Route::group(['prefix' => 'shop/{tenantSlug}'], function () {
                    Route::get('/', [HomeController::class, 'index'])
                        ->name('shop.tenant.home');
                    Route::get('contact-us', [HomeController::class, 'contactUs'])
                        ->name('shop.tenant.contact_us');
                    Route::get('search', [SearchController::class, 'index'])
                        ->name('shop.tenant.search');
                });
            }
        });
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('tenant', ResolveTenant::class);
    }
}
