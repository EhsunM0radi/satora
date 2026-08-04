<?php

namespace Webkul\POS\Support;

use Webkul\Tenant\Models\Tenant;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantIsolationScope);

        static::creating(function ($model) {
            if (! $model->tenant_id && static::hasTenantContext()) {
                $model->tenant_id = app('current_tenant')->id;
            }
        });

        static::saving(function ($model) {
            if (! $model->tenant_id && static::hasTenantContext()) {
                $model->tenant_id = app('current_tenant')->id;
            }
        });
    }

    protected static function hasTenantContext(): bool
    {
        return app()->bound('current_tenant') && app('current_tenant') !== null;
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope(TenantIsolationScope::class);
    }
}
