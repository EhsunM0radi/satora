<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pos_orders') && ! Schema::hasColumn('pos_orders', 'seller_id')) {
            DB::statement('ALTER TABLE `pos_orders` ADD COLUMN `seller_id` INT UNSIGNED NULL AFTER `admin_user_id`');
        }
    }

    public function down(): void
    {
        // No reverse needed
    }
};
