<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosAuditLog as PosAuditLogContract;
use Webkul\POS\Contracts\PosAuditLogModel;
use Webkul\POS\Support\BelongsToTenant;
use Webkul\User\Models\Admin;

class PosAuditLog extends Model implements PosAuditLogContract, PosAuditLogModel
{
    use BelongsToTenant;

    protected $table = 'pos_audit_logs';

    protected $fillable = [
        'tenant_id',
        'admin_user_id',
        'event_type',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_user_id');
    }
}
