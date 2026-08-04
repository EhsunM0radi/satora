<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosSession as PosSessionContract;
use Webkul\POS\Contracts\PosSessionModel;
use Webkul\POS\Support\BelongsToTenant;
use Webkul\User\Models\Admin;

class PosSession extends Model implements PosSessionContract, PosSessionModel
{
    use BelongsToTenant;

    protected $table = 'pos_sessions';

    protected $fillable = [
        'tenant_id',
        'pos_terminal_id',
        'admin_user_id',
        'session_number',
        'status',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'difference',
        'notes',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:4',
            'closing_balance' => 'decimal:4',
            'expected_balance' => 'decimal:4',
            'difference' => 'decimal:4',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function cashier()
    {
        return $this->belongsTo(Admin::class, 'admin_user_id');
    }

    public function cashRegisters()
    {
        return $this->hasMany(PosCashRegister::class, 'pos_session_id');
    }

    public function cashMovements()
    {
        return $this->hasMany(PosCashMovement::class, 'pos_session_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function orders()
    {
        return $this->hasMany(PosOrder::class, 'pos_session_id');
    }
}
