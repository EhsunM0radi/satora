<?php

namespace Webkul\POS\Observers;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Exceptions\TenantIsolationException;

class TenantIsolationObserver
{
    public function saving(Model $model): void
    {
        $tenant = app('current_tenant');

        if (! $tenant) {
            return; // Allow cross-tenant admin operations
        }

        if ($model->tenant_id && $model->tenant_id !== $tenant->id) {
            throw TenantIsolationException::crossTenantViolation($model);
        }

        if (! $model->tenant_id) {
            $model->tenant_id = $tenant->id;
        }
    }
}
