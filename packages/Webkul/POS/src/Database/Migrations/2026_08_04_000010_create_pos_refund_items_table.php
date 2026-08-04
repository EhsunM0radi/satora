<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_refund_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_refund_id');
            $table->unsignedBigInteger('pos_order_item_id');
            $table->decimal('quantity', 12, 4);
            $table->decimal('amount', 15, 4);
            $table->string('reason', 255)->nullable();
            $table->boolean('restock')->default(true);
            $table->timestamp('created_at')->nullable();

            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_refund_id', 'idx_refund');
            $table->index('pos_order_item_id', 'idx_order_item');

            $table->foreign('tenant_id', 'fk_pos_refund_items_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_refund_id', 'fk_pos_refund_items_refund')
                ->references('id')->on('pos_refunds')
                ->onDelete('cascade');

            $table->foreign('pos_order_item_id', 'fk_pos_refund_items_order_item')
                ->references('id')->on('pos_order_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_refund_items');
    }
};
