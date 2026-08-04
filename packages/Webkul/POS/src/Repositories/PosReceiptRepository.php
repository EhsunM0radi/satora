<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosReceipt;

class PosReceiptRepository extends Repository
{
    public function model(): string
    {
        return PosReceipt::class;
    }
}
