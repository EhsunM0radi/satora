<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_order_id')->nullable();
            $table->unsignedBigInteger('pos_refund_id')->nullable();
            $table->string('receipt_number', 50);
            $table->enum('type', ['sale', 'refund', 'exchange', 'opening', 'closing', 'cash_movement']);
            $table->string('template', 50)->default('default');
            $table->enum('delivery_method', ['print', 'email', 'sms', 'digital', 'none'])->default('print');
            $table->string('recipient_email', 255)->nullable();
            $table->string('recipient_phone', 20)->nullable();
            $table->text('content_html')->nullable();
            $table->text('qr_code_data')->nullable();
            $table->boolean('printed')->default(false);
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();

            $table->unique('receipt_number', 'uk_receipt_number');
            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_order_id', 'idx_order');
            $table->index('pos_refund_id', 'idx_refund');

            $table->foreign('tenant_id', 'fk_pos_receipts_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_order_id', 'fk_pos_receipts_order')
                ->references('id')->on('pos_orders')
                ->onDelete('set null');

            $table->foreign('pos_refund_id', 'fk_pos_receipts_refund')
                ->references('id')->on('pos_refunds')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_receipts');
    }
};
