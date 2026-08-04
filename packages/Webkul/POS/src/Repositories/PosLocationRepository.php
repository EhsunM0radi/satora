<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosLocation;

class PosLocationRepository extends Repository
{
    public function model(): string
    {
        return PosLocation::class;
    }
}
