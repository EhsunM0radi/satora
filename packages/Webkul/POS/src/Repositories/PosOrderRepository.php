<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosOrder;

class PosOrderRepository extends Repository
{
    public function model(): string
    {
        return PosOrder::class;
    }
}
