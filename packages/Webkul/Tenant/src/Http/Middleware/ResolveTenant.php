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
            // No tenant resolved — let the request through for central/install routes
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
}
