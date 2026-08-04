<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosOfflineQueue as PosOfflineQueueContract;
use Webkul\POS\Contracts\PosOfflineQueueModel;
use Webkul\POS\Support\BelongsToTenant;

class PosOfflineQueue extends Model implements PosOfflineQueueContract, PosOfflineQueueModel
{
    use BelongsToTenant;

    protected $table = 'pos_offline_queues';

    protected $fillable = [
        'tenant_id',
        'pos_terminal_id',
        'action',
        'payload',
        'status',
        'local_id',
        'server_id',
        'attempts',
        'last_error',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'synced_at' => 'datetime',
        ];
    }

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'pos_terminal_id');
    }
}
