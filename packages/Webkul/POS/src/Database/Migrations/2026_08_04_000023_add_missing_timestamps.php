<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'pos_cash_movements',
            'pos_audit_logs',
            'pos_hardware_events',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'updated_at')) {
                DB::statement("ALTER TABLE `{$table}` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`");
            }
        }
    }

    public function down(): void
    {
        // No reverse needed
    }
};
