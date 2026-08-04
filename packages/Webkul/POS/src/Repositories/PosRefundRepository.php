<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosRefund;

class PosRefundRepository extends Repository
{
    public function model(): string
    {
        return PosRefund::class;
    }
}
