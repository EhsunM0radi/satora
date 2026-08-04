<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosSession;

class PosSessionRepository extends Repository
{
    public function model(): string
    {
        return PosSession::class;
    }
}
