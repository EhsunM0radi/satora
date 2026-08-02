<?php

namespace Webkul\Tenant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Webkul\Tenant\TenantResolver;

class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $resolver = app(TenantResolver::class);
        $tenant = $resolver->resolve($request);

        if (! $tenant) {
            // No tenant resolved from domain/path — try admin's tenant for locale
            $this->applyAdminTenantLocale();

            return $next($request);
        }

        // Set the tenant in the app container
        app()->instance('current_tenant', $tenant);

        // Apply locale from tenant
        if ($tenant->getLocale()) {
            app()->setLocale($tenant->getLocale());
        }

        return $next($request);
    }

    /**
     * When on the central domain (no tenant resolved), apply the
     * authenticated admin's tenant locale if available.
     */
    protected function applyAdminTenantLocale(): void
    {
        if (! auth()->guard('admin')->check()) {
            return;
        }

        $admin = auth()->guard('admin')->user();
        $tenant = $admin->tenants()->first();

        if ($tenant && $tenant->getLocale()) {
            app()->setLocale($tenant->getLocale());
        }
    }
}
