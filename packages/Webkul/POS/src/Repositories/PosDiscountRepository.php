<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosDiscount;

class PosDiscountRepository extends Repository
{
    public function model(): string
    {
        return PosDiscount::class;
    }
}
