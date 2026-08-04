<?php

namespace Webkul\POS\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class TenantIsolationException extends Exception
{
    public static function crossTenantViolation(Model $model): self
    {
        $class = class_basename($model);

        return new self(
            "Cross-tenant violation: {$class} ID={$model->getKey()} belongs to tenant {$model->tenant_id} but current tenant is different."
        );
    }

    public static function missingTenant(string $modelClass): self
    {
        return new self(
            "Tenant context required: {$modelClass} cannot be saved without a tenant_id when a tenant context is active."
        );
    }
}
