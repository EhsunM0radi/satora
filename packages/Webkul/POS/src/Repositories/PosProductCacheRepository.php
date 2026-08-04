<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosProductCache;

class PosProductCacheRepository extends Repository
{
    public function model(): string
    {
        return PosProductCache::class;
    }
}
