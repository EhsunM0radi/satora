<?php

namespace Webkul\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\POS\Contracts\PosEmployeeAssignment as PosEmployeeAssignmentContract;
use Webkul\POS\Contracts\PosEmployeeAssignmentModel;
use Webkul\POS\Support\BelongsToTenant;
use Webkul\User\Models\Admin;

class PosEmployeeAssignment extends Model implements PosEmployeeAssignmentContract, PosEmployeeAssignmentModel
{
    use BelongsToTenant;

    protected $table = 'pos_employee_assignments';

    protected $fillable = [
        'tenant_id',
        'admin_user_id',
        'pos_employee_role_id',
        'pos_location_id',
        'pin_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_user_id');
    }

    public function role()
    {
        return $this->belongsTo(PosEmployeeRole::class, 'pos_employee_role_id');
    }

    public function location()
    {
        return $this->belongsTo(PosLocation::class, 'pos_location_id');
    }
}
