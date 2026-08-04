<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosOrderItem;

class PosOrderItemRepository extends Repository
{
    public function model(): string
    {
        return PosOrderItem::class;
    }
}
