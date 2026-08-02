<?php

namespace Webkul\Tenant;

use Illuminate\Http\Request;
use Webkul\Tenant\Contracts\Tenant;
use Webkul\Tenant\Repositories\TenantRepository;

/**
 * Resolves the current tenant from the request.
 *
 * In production: domain or subdomain (e.g. mystore.satora.com).
 * In local: path-based (satora.test/shop/mystore) or subdomain (mystore.satora.test).
 */
class TenantResolver
{
    protected ?Tenant $current = null;

    public function __construct(
        protected TenantRepository $repository
    ) {}

    /**
     * Resolve tenant from the request.
     */
    public function resolve(Request $request): ?Tenant
    {
        if ($this->current) {
            return $this->current;
        }

        $host = $request->getHost();

        // Try exact domain match
        $tenant = $this->repository->findByDomain($host);
        if ($tenant && $tenant->isActive()) {
            return $this->current = $tenant;
        }

        // Try subdomain: {slug}.satora.test
        if (str_contains($host, '.')) {
            $parts = explode('.', $host);
            if (count($parts) >= 3) {
                $slug = $parts[0];
                $tenant = $this->repository->findBySlug($slug);
                if ($tenant && $tenant->isActive()) {
                    return $this->current = $tenant;
                }
            }
        }

        // Local fallback: path-based — satora.test/shop/{slug}
        if (app()->environment('local')) {
            $path = trim($request->path(), '/');
            if (preg_match('#^shop/([a-z0-9_-]+)(/.*)?$#', $path, $matches)) {
                $tenant = $this->repository->findBySlug($matches[1]);
                if ($tenant && $tenant->isActive()) {
                    return $this->current = $tenant;
                }
            }
        }

        return null;
    }

    public function current(): ?Tenant
    {
        return $this->current;
    }

    public function setCurrent(Tenant $tenant): void
    {
        $this->current = $tenant;
    }

    public function requireId(): int
    {
        if (! $this->current) {
            throw new \RuntimeException('No tenant resolved.');
        }

        return $this->current->getId();
    }

    public function id(): ?int
    {
        return $this->current?->getId();
    }
}
