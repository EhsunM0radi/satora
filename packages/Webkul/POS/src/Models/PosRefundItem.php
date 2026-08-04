<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosRefundItem as PosRefundItemContract;
use Webkul\POS\Contracts\PosRefundItemModel;
use Webkul\POS\Support\BelongsToTenant;

class PosRefundItem extends Model implements PosRefundItemContract, PosRefundItemModel
{
    use BelongsToTenant;

    protected $table = 'pos_refund_items';

    protected $fillable = [
        'tenant_id',
        'pos_refund_id',
        'pos_order_item_id',
        'quantity',
        'amount',
        'reason',
        'restock',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'amount' => 'decimal:4',
            'restock' => 'boolean',
        ];
    }

    public function refund()
    {
        return $this->belongsTo(PosRefund::class, 'pos_refund_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(PosOrderItem::class, 'pos_order_item_id');
    }
}
