<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosCashRegister as PosCashRegisterContract;
use Webkul\POS\Contracts\PosCashRegisterModel;
use Webkul\POS\Support\BelongsToTenant;

class PosCashRegister extends Model implements PosCashRegisterContract, PosCashRegisterModel
{
    use BelongsToTenant;

    protected $table = 'pos_cash_registers';

    protected $fillable = [
        'tenant_id',
        'pos_terminal_id',
        'pos_session_id',
        'name',
        'type',
        'current_balance',
        'currency',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:4',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function session()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function cashMovements()
    {
        return $this->hasMany(PosCashMovement::class, 'pos_cash_register_id');
    }

    public function payments()
    {
        return $this->hasMany(PosPayment::class, 'pos_cash_register_id');
    }
}
