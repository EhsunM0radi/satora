<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosHardwareEvent as PosHardwareEventContract;
use Webkul\POS\Contracts\PosHardwareEventModel;
use Webkul\POS\Support\BelongsToTenant;

class PosHardwareEvent extends Model implements PosHardwareEventContract, PosHardwareEventModel
{
    use BelongsToTenant;

    protected $table = 'pos_hardware_events';

    protected $fillable = [
        'tenant_id',
        'pos_terminal_id',
        'device_type',
        'event_type',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }
}
