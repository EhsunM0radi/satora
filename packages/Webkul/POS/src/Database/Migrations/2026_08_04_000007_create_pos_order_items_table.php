<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_order_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variant_id')->nullable();
            $table->unsignedInteger('inventory_source_id')->nullable();
            $table->string('name', 255);
            $table->string('sku', 100)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('discount_amount', 15, 4)->default(0.0000);
            $table->decimal('tax_amount', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4);
            $table->decimal('tax_rate', 8, 4)->default(0.0000);
            $table->string('serial_number', 100)->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_refunded')->default(false);
            $table->decimal('refunded_quantity', 12, 4)->default(0.0000);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_order_id', 'idx_order');
            $table->index('product_id', 'idx_product');
            $table->index('variant_id', 'idx_variant');
            $table->index('serial_number', 'idx_serial');

            $table->foreign('tenant_id', 'fk_pos_order_items_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_order_id', 'fk_pos_order_items_order')
                ->references('id')->on('pos_orders')
                ->onDelete('cascade');

            $table->foreign('product_id', 'fk_pos_order_items_product')
                ->references('id')->on('products')
                ->onDelete('restrict');

            $table->foreign('variant_id', 'fk_pos_order_items_variant')
                ->references('id')->on('product_flat')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_items');
    }
};
