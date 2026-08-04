<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosPayment;

class PosPaymentRepository extends Repository
{
    public function model(): string
    {
        return PosPayment::class;
    }
}
