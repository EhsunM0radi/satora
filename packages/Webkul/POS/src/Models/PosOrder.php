<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;
use Webkul\POS\Contracts\PosOrder as PosOrderContract;
use Webkul\POS\Contracts\PosOrderModel;
use Webkul\POS\Support\BelongsToTenant;
use Webkul\User\Models\Admin;

class PosOrder extends Model implements PosOrderContract, PosOrderModel
{
    use BelongsToTenant;

    protected $table = 'pos_orders';

    protected $fillable = [
        'tenant_id',
        'pos_session_id',
        'pos_terminal_id',
        'customer_id',
        'admin_user_id',
        'seller_id',
        'order_number',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'total',
        'paid_amount',
        'due_amount',
        'currency',
        'tax_inclusive',
        'notes',
        'held_at',
        'completed_at',
        'voided_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:4',
            'discount_amount' => 'decimal:4',
            'tax_amount' => 'decimal:4',
            'shipping_amount' => 'decimal:4',
            'total' => 'decimal:4',
            'paid_amount' => 'decimal:4',
            'due_amount' => 'decimal:4',
            'tax_inclusive' => 'boolean',
            'held_at' => 'datetime',
            'completed_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function session()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function cashier()
    {
        return $this->belongsTo(Admin::class, 'admin_user_id');
    }

    public function items()
    {
        return $this->hasMany(PosOrderItem::class, 'pos_order_id');
    }

    public function payments()
    {
        return $this->hasMany(PosPayment::class, 'pos_order_id');
    }

    public function refunds()
    {
        return $this->hasMany(PosRefund::class, 'pos_order_id');
    }

    public function exchangeAsOriginal()
    {
        return $this->hasMany(PosExchange::class, 'original_order_id');
    }

    public function exchangeAsNew()
    {
        return $this->hasMany(PosExchange::class, 'new_order_id');
    }

    public function receipts()
    {
        return $this->hasMany(PosReceipt::class, 'pos_order_id');
    }

    public function inventoryReservations()
    {
        return $this->hasMany(PosInventoryReservation::class, 'pos_order_id');
    }
}
