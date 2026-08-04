<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosInventoryReservation as PosInventoryReservationContract;
use Webkul\POS\Contracts\PosInventoryReservationModel;
use Webkul\POS\Support\BelongsToTenant;

class PosInventoryReservation extends Model implements PosInventoryReservationContract, PosInventoryReservationModel
{
    use BelongsToTenant;

    protected $table = 'pos_inventory_reservations';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_id',
        'inventory_source_id',
        'pos_order_id',
        'pos_order_item_id',
        'quantity',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'expires_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(PosOrderItem::class, 'pos_order_item_id');
    }
}
