<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_order_id');
            $table->unsignedBigInteger('pos_cash_register_id')->nullable();
            $table->unsignedInteger('payment_method_id');
            $table->string('payment_method_code', 50);
            $table->decimal('amount', 15, 4);
            $table->string('reference_number', 100)->nullable();
            $table->enum('status', ['pending', 'approved', 'declined', 'refunded'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_order_id', 'idx_order');
            $table->index('pos_cash_register_id', 'idx_register');
            $table->index('payment_method_code', 'idx_method');

            $table->foreign('tenant_id', 'fk_pos_payments_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_order_id', 'fk_pos_payments_order')
                ->references('id')->on('pos_orders')
                ->onDelete('cascade');

            $table->foreign('pos_cash_register_id', 'fk_pos_payments_register')
                ->references('id')->on('pos_cash_registers')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_payments');
    }
};
