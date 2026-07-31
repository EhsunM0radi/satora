<?php

namespace Webkul\Tenant\Providers;

use Konekt\Concord\BaseModuleServiceProvider;
use Webkul\Tenant\Models\Tenant;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Tenant::class,
    ];
}
