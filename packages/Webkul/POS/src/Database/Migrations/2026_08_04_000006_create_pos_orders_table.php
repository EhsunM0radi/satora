<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_session_id');
            $table->unsignedBigInteger('pos_terminal_id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->unsignedInteger('admin_user_id');
            $table->string('order_number', 50);
            $table->enum('status', ['draft', 'held', 'completed', 'voided', 'refunded', 'partially_refunded', 'exchanged'])->default('draft');
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->decimal('discount_amount', 15, 4)->default(0.0000);
            $table->decimal('tax_amount', 15, 4)->default(0.0000);
            $table->decimal('shipping_amount', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4)->default(0.0000);
            $table->decimal('paid_amount', 15, 4)->default(0.0000);
            $table->decimal('due_amount', 15, 4)->default(0.0000);
            $table->string('currency', 3)->default('IRR');
            $table->boolean('tax_inclusive')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('held_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();

            $table->unique('order_number', 'uk_order_number');
            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_session_id', 'idx_session');
            $table->index('pos_terminal_id', 'idx_terminal');
            $table->index('customer_id', 'idx_customer');
            $table->index('admin_user_id', 'idx_cashier');
            $table->index('status', 'idx_status');

            $table->foreign('tenant_id', 'fk_pos_orders_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_session_id', 'fk_pos_orders_session')
                ->references('id')->on('pos_sessions')
                ->onDelete('cascade');

            $table->foreign('pos_terminal_id', 'fk_pos_orders_terminal')
                ->references('id')->on('pos_terminals')
                ->onDelete('cascade');

            $table->foreign('customer_id', 'fk_pos_orders_customer')
                ->references('id')->on('customers')
                ->onDelete('set null');

            $table->foreign('admin_user_id', 'fk_pos_orders_admin')
                ->references('id')->on('admins')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_orders');
    }
};
