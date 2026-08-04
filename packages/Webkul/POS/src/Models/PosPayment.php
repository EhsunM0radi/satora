<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosPayment as PosPaymentContract;
use Webkul\POS\Contracts\PosPaymentModel;
use Webkul\POS\Support\BelongsToTenant;

class PosPayment extends Model implements PosPaymentContract, PosPaymentModel
{
    use BelongsToTenant;

    protected $table = 'pos_payments';

    protected $fillable = [
        'tenant_id',
        'pos_order_id',
        'pos_cash_register_id',
        'payment_method_id',
        'payment_method_code',
        'amount',
        'reference_number',
        'status',
        'gateway_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function cashRegister()
    {
        return $this->belongsTo(PosCashRegister::class, 'pos_cash_register_id');
    }
}
