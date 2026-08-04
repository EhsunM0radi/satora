<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosProductCache as PosProductCacheContract;
use Webkul\POS\Contracts\PosProductCacheModel;
use Webkul\POS\Support\BelongsToTenant;

class PosProductCache extends Model implements PosProductCacheContract, PosProductCacheModel
{
    use BelongsToTenant;

    protected $table = 'pos_product_cache';

    protected $fillable = [
        'tenant_id',
        'pos_terminal_id',
        'product_id',
        'cached_data',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'cached_data' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }
}
