<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosTerminal as PosTerminalContract;
use Webkul\POS\Contracts\PosTerminalModel;
use Webkul\POS\Support\BelongsToTenant;

class PosTerminal extends Model implements PosTerminalContract, PosTerminalModel
{
    use BelongsToTenant;

    protected $table = 'pos_terminals';

    protected $fillable = [
        'tenant_id',
        'pos_location_id',
        'name',
        'code',
        'status',
        'hardware_profile',
        'settings',
        'last_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'hardware_profile' => 'array',
            'settings' => 'array',
            'last_sync_at' => 'datetime',
        ];
    }

    public function location()
    {
        return $this->belongsTo(PosLocation::class, 'pos_location_id');
    }

    public function sessions()
    {
        return $this->hasMany(PosSession::class, 'pos_terminal_id');
    }

    public function currentSession()
    {
        return $this->hasOne(PosSession::class, 'pos_terminal_id')->where('status', 'open')->latest();
    }
}
