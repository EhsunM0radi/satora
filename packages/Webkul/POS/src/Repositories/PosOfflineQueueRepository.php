<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosOfflineQueue;

class PosOfflineQueueRepository extends Repository
{
    public function model(): string
    {
        return PosOfflineQueue::class;
    }
}
