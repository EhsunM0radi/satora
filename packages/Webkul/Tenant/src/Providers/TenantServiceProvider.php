<?php

namespace Webkul\Tenant\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Shop\Http\Controllers\HomeController;
use Webkul\Shop\Http\Controllers\SearchController;
use Webkul\Tenant\Contracts\SmsDriver;
use Webkul\Tenant\Http\Controllers\OnboardingController;
use Webkul\Tenant\Http\Controllers\SignupController;
use Webkul\Tenant\Http\Controllers\TenantController;
use Webkul\Tenant\Http\Middleware\ResolveTenant;
use Webkul\Tenant\Repositories\TenantRepository;
use Webkul\Tenant\Services\OtpService;
use Webkul\Tenant\Services\SmsDrivers\LogDriver;
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

        // SMS driver — swap binding for production provider
        $this->app->bind(SmsDriver::class, LogDriver::class);

        // OTP service
        $this->app->singleton(OtpService::class, function ($app) {
            return new OtpService($app->make(SmsDriver::class));
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
        Route::group(['prefix' => 'api/v1', 'middleware' => ['api', 'auth:admin']], function () {
            Route::post('tenant', [TenantController::class, 'store'])
                ->name('api.tenant.store');
        });

        Route::group(['middleware' => 'web'], function () {
            Route::get('signup', [SignupController::class, 'show'])
                ->name('signup.show');
            Route::post('signup', [SignupController::class, 'store'])
                ->name('signup.store')
                ->middleware('throttle:10,1');

            // OTP signup flow — rate-limited to prevent brute-force
            Route::post('signup/otp/send', [SignupController::class, 'sendOtp'])
                ->name('signup.otp.send')
                ->middleware('throttle:3,1');
            Route::get('signup/otp/verify', [SignupController::class, 'showOtpVerify'])
                ->name('signup.otp.verify');
            Route::post('signup/otp/verify', [SignupController::class, 'verifyOtp'])
                ->name('signup.otp.verify.submit')
                ->middleware('throttle:5,2');

            Route::middleware('admin')->group(function () {
                Route::get('onboarding/{step?}', [OnboardingController::class, 'show'])
                    ->name('onboarding.show');
                Route::post('onboarding', [OnboardingController::class, 'store'])
                    ->name('onboarding.store');
            });

            // Local: path-based tenant storefront — localhost/shop/{slug}
            if (app()->environment('local')) {
                Route::group(['prefix' => 'shop/{tenantSlug}'], function () {
                    // Storefront
                    Route::get('/', [HomeController::class, 'index'])
                        ->name('shop.tenant.home');
                    Route::get('contact-us', [HomeController::class, 'contactUs'])
                        ->name('shop.tenant.contact_us');
                    Route::get('search', [SearchController::class, 'index'])
                        ->name('shop.tenant.search');

                    // Admin — store tenant in session then redirect to central admin
                    Route::any('admin/{any?}', function (string $tenantSlug) {
                        $resolver = app(TenantResolver::class);
                        session()->put('impersonated_tenant_id', $resolver->id());

                        // Strip "shop/{slug}" prefix, redirect to /admin/...
                        $target = preg_replace('#^shop/'.preg_quote($tenantSlug, '#').'#', '', request()->path());

                        return redirect('/'.ltrim($target, '/'));
                    })->where('any', '.*')->name('shop.tenant.admin');
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
