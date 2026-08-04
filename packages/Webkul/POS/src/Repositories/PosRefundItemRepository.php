<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosRefundItem;

class PosRefundItemRepository extends Repository
{
    public function model(): string
    {
        return PosRefundItem::class;
    }
}
