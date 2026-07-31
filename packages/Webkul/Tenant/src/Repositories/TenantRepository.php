<?php

namespace Webkul\Tenant\Repositories;

use Illuminate\Support\Collection;
use Webkul\Core\Eloquent\Repository;
use Webkul\Tenant\Contracts\Tenant;

class TenantRepository extends Repository
{
    public function model(): string
    {
        return Tenant::class;
    }

    public function findByDomain(string $domain): ?Tenant
    {
        return $this->findOneByField('domain', $domain);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return $this->findOneByField('slug', $slug);
    }

    public function findActive(): Collection
    {
        return $this->findByField('is_active', true);
    }
}
