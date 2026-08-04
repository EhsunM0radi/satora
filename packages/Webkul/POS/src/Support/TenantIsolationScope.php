<?php

namespace Webkul\POS\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantIsolationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenant = app('current_tenant');

        if ($tenant) {
            $builder->where($model->getTable().'.tenant_id', $tenant->id);
        }
    }
}
