<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosExchange;

class PosExchangeRepository extends Repository
{
    public function model(): string
    {
        return PosExchange::class;
    }
}
