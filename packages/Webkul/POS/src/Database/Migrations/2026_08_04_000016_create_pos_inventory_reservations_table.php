<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variant_id')->nullable();
            $table->unsignedInteger('inventory_source_id');
            $table->unsignedBigInteger('pos_order_id')->nullable();
            $table->unsignedBigInteger('pos_order_item_id')->nullable();
            $table->decimal('quantity', 12, 4);
            $table->enum('status', ['reserved', 'confirmed', 'released'])->default('reserved');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id', 'idx_tenant');
            $table->index('product_id', 'idx_product');
            $table->index('pos_order_id', 'idx_order');
            $table->index('inventory_source_id', 'idx_source');
            $table->index(['status', 'expires_at'], 'idx_status_expires');

            $table->foreign('tenant_id', 'fk_pos_inventory_reservations_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('product_id', 'fk_pos_inventory_reservations_product')
                ->references('id')->on('products')
                ->onDelete('restrict');

            $table->foreign('pos_order_id', 'fk_pos_inventory_reservations_order')
                ->references('id')->on('pos_orders')
                ->onDelete('cascade');

            $table->foreign('pos_order_item_id', 'fk_pos_inventory_reservations_order_item')
                ->references('id')->on('pos_order_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_inventory_reservations');
    }
};
