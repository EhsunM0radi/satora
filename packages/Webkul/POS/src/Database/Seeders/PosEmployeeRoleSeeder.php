<?php

namespace Webkul\POS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosEmployeeRoleSeeder extends Seeder
{
    protected array $defaultRoles = [
        'owner' => [
            'name' => 'Owner',
            'is_system' => 1,
            'permissions' => [
                'pos.create_sale' => true, 'pos.cancel_sale' => true, 'pos.process_refund' => true,
                'pos.apply_discount' => true, 'pos.change_price' => true, 'pos.open_drawer' => true,
                'pos.view_reports' => true, 'pos.edit_products' => true, 'pos.access_customers' => true,
                'pos.manage_sessions' => true, 'pos.manage_employees' => true, 'pos.view_analytics' => true,
            ],
        ],
        'manager' => [
            'name' => 'Manager',
            'is_system' => 1,
            'permissions' => [
                'pos.create_sale' => true, 'pos.cancel_sale' => true, 'pos.process_refund' => true,
                'pos.apply_discount' => true, 'pos.change_price' => true, 'pos.open_drawer' => true,
                'pos.view_reports' => true, 'pos.edit_products' => false, 'pos.access_customers' => true,
                'pos.manage_sessions' => true, 'pos.manage_employees' => false, 'pos.view_analytics' => true,
            ],
        ],
        'supervisor' => [
            'name' => 'Supervisor',
            'is_system' => 1,
            'permissions' => [
                'pos.create_sale' => true, 'pos.cancel_sale' => false, 'pos.process_refund' => true,
                'pos.apply_discount' => true, 'pos.change_price' => false, 'pos.open_drawer' => true,
                'pos.view_reports' => true, 'pos.edit_products' => false, 'pos.access_customers' => true,
                'pos.manage_sessions' => false, 'pos.manage_employees' => false, 'pos.view_analytics' => false,
            ],
        ],
        'cashier' => [
            'name' => 'Cashier',
            'is_system' => 1,
            'permissions' => [
                'pos.create_sale' => true, 'pos.cancel_sale' => false, 'pos.process_refund' => false,
                'pos.apply_discount' => false, 'pos.change_price' => false, 'pos.open_drawer' => true,
                'pos.view_reports' => false, 'pos.edit_products' => false, 'pos.access_customers' => true,
                'pos.manage_sessions' => false, 'pos.manage_employees' => false, 'pos.view_analytics' => false,
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->defaultRoles as $code => $roleData) {
            DB::table('pos_employee_roles')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $roleData['name'],
                    'permissions' => json_encode($roleData['permissions']),
                    'is_system' => $roleData['is_system'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
