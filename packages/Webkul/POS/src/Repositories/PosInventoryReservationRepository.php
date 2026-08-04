<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosInventoryReservation;

class PosInventoryReservationRepository extends Repository
{
    public function model(): string
    {
        return PosInventoryReservation::class;
    }
}
