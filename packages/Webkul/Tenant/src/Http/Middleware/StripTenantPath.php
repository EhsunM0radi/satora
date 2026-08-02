<?php

namespace Webkul\Tenant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Strips the "shop/{slug}" prefix from the request URI so that
 * existing shop routes match normally.
 *
 * Used only in local environment for path-based tenant access.
 */
class StripTenantPath
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->path();

        if (preg_match('#^shop/([a-z0-9_-]+)(/.*)?$#', $path, $matches)) {
            $newPath = $matches[2] ?: '/';

            // Rewrite REQUEST_URI so Laravel's router matches the unprefixed path
            $request->server->set('REQUEST_URI', $newPath);

            // Keep the original for reference
            $request->attributes->set('tenant_slug', $matches[1]);
        }

        return $next($request);
    }
}
