<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosExchange as PosExchangeContract;
use Webkul\POS\Contracts\PosExchangeModel;
use Webkul\POS\Support\BelongsToTenant;
use Webkul\User\Models\Admin;

class PosExchange extends Model implements PosExchangeContract, PosExchangeModel
{
    use BelongsToTenant;

    protected $table = 'pos_exchanges';

    protected $fillable = [
        'tenant_id',
        'original_order_id',
        'new_order_id',
        'pos_session_id',
        'admin_user_id',
        'exchange_number',
        'price_difference',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'price_difference' => 'decimal:4',
        ];
    }

    public function originalOrder()
    {
        return $this->belongsTo(PosOrder::class, 'original_order_id');
    }

    public function newOrder()
    {
        return $this->belongsTo(PosOrder::class, 'new_order_id');
    }

    public function session()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function cashier()
    {
        return $this->belongsTo(Admin::class, 'admin_user_id');
    }
}
