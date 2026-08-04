<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosHardwareEvent;

class PosHardwareEventRepository extends Repository
{
    public function model(): string
    {
        return PosHardwareEvent::class;
    }
}
