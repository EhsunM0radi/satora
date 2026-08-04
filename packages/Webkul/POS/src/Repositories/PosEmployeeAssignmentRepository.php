<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosEmployeeAssignment;

class PosEmployeeAssignmentRepository extends Repository
{
    public function model(): string
    {
        return PosEmployeeAssignment::class;
    }
}
