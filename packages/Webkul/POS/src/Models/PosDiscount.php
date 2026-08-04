<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosDiscount as PosDiscountContract;
use Webkul\POS\Contracts\PosDiscountModel;
use Webkul\POS\Support\BelongsToTenant;

class PosDiscount extends Model implements PosDiscountContract, PosDiscountModel
{
    use BelongsToTenant;

    protected $table = 'pos_discounts';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'applies_to',
        'is_active',
        'starts_at',
        'ends_at',
        'usage_limit',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'min_order_amount' => 'decimal:4',
            'max_discount_amount' => 'decimal:4',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
