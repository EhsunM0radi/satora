<?php

namespace Webkul\POS\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\POS\Models\PosTerminal;

class PosTerminalRepository extends Repository
{
    public function model(): string
    {
        return PosTerminal::class;
    }
}
