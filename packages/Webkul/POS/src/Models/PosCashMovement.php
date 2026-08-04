<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosCashMovement as PosCashMovementContract;
use Webkul\POS\Contracts\PosCashMovementModel;
use Webkul\POS\Support\BelongsToTenant;
use Webkul\User\Models\Admin;

class PosCashMovement extends Model implements PosCashMovementContract, PosCashMovementModel
{
    use BelongsToTenant;

    protected $table = 'pos_cash_movements';

    protected $fillable = [
        'tenant_id',
        'pos_session_id',
        'pos_cash_register_id',
        'admin_user_id',
        'type',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'balance_after' => 'decimal:4',
        ];
    }

    public function session()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function cashRegister()
    {
        return $this->belongsTo(PosCashRegister::class, 'pos_cash_register_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_user_id');
    }
}
