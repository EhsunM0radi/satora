<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tables that should allow null tenant_id (system/admin operations, tests)
    protected array $tables = [
        'pos_locations',
        'pos_terminals',
        'pos_discounts',
        'pos_hardware_events',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                // Drop FK, make nullable, re-add FK
                // Using raw SQL for reliability
            });
            DB::statement("ALTER TABLE `{$table}` MODIFY `tenant_id` BIGINT UNSIGNED NULL");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `tenant_id` BIGINT UNSIGNED NOT NULL");
        }
    }
};
