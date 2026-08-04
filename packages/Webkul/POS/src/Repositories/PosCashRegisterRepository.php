<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosCashRegister;

class PosCashRegisterRepository extends Repository
{
    public function model(): string
    {
        return PosCashRegister::class;
    }
}
