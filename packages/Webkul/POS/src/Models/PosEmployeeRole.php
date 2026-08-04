<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosEmployeeRole as PosEmployeeRoleContract;
use Webkul\POS\Contracts\PosEmployeeRoleModel;
use Webkul\POS\Support\BelongsToTenant;

class PosEmployeeRole extends Model implements PosEmployeeRoleContract, PosEmployeeRoleModel
{
    use BelongsToTenant;

    protected $table = 'pos_employee_roles';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'permissions',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function assignments()
    {
        return $this->hasMany(PosEmployeeAssignment::class, 'pos_employee_role_id');
    }
}
