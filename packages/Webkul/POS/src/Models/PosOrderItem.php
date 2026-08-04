<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosOrderItem as PosOrderItemContract;
use Webkul\POS\Contracts\PosOrderItemModel;
use Webkul\POS\Support\BelongsToTenant;

class PosOrderItem extends Model implements PosOrderItemContract, PosOrderItemModel
{
    use BelongsToTenant;

    protected $table = 'pos_order_items';

    protected $fillable = [
        'tenant_id',
        'pos_order_id',
        'product_id',
        'variant_id',
        'inventory_source_id',
        'name',
        'sku',
        'barcode',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'total',
        'tax_rate',
        'serial_number',
        'batch_number',
        'expiry_date',
        'is_refunded',
        'refunded_quantity',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount_amount' => 'decimal:4',
            'tax_amount' => 'decimal:4',
            'total' => 'decimal:4',
            'tax_rate' => 'decimal:4',
            'is_refunded' => 'boolean',
            'refunded_quantity' => 'decimal:4',
            'metadata' => 'array',
            'expiry_date' => 'date',
        ];
    }

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function refundItems()
    {
        return $this->hasMany(PosRefundItem::class, 'pos_order_item_id');
    }
}
