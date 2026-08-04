<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosAuditLog;

class PosAuditLogRepository extends Repository
{
    public function model(): string
    {
        return PosAuditLog::class;
    }
}
