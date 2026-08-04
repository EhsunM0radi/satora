<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosRefund as PosRefundContract;
use Webkul\POS\Contracts\PosRefundModel;
use Webkul\POS\Support\BelongsToTenant;
use Webkul\User\Models\Admin;

class PosRefund extends Model implements PosRefundContract, PosRefundModel
{
    use BelongsToTenant;

    protected $table = 'pos_refunds';

    protected $fillable = [
        'tenant_id',
        'pos_order_id',
        'pos_session_id',
        'admin_user_id',
        'refund_number',
        'refund_method',
        'total_amount',
        'reason',
        'status',
        'notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:4',
            'completed_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function session()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function cashier()
    {
        return $this->belongsTo(Admin::class, 'admin_user_id');
    }

    public function items()
    {
        return $this->hasMany(PosRefundItem::class, 'pos_refund_id');
    }

    public function receipt()
    {
        return $this->hasOne(PosReceipt::class, 'pos_refund_id');
    }
}
