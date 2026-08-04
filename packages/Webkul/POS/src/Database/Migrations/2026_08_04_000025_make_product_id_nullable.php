<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pos_order_items')) {
            return;
        }

        // Drop FK constraint
        try {
            DB::statement('ALTER TABLE `pos_order_items` DROP FOREIGN KEY `fk_pos_order_items_product`');
        } catch (Exception $e) {
            // FK may not exist — continue
        }

        // Make product_id nullable
        DB::statement('ALTER TABLE `pos_order_items` MODIFY `product_id` INT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `pos_order_items` MODIFY `product_id` INT UNSIGNED NOT NULL');
    }
};
