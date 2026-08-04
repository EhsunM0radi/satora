<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_order_id');
            $table->unsignedBigInteger('pos_session_id');
            $table->unsignedInteger('admin_user_id');
            $table->string('refund_number', 50);
            $table->enum('refund_method', ['cash', 'card', 'store_credit', 'wallet', 'original_payment']);
            $table->decimal('total_amount', 15, 4);
            $table->string('reason', 255)->nullable();
            $table->enum('status', ['pending', 'approved', 'completed', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique('refund_number', 'uk_refund_number');
            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_order_id', 'idx_order');
            $table->index('pos_session_id', 'idx_session');

            $table->foreign('tenant_id', 'fk_pos_refunds_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_order_id', 'fk_pos_refunds_order')
                ->references('id')->on('pos_orders')
                ->onDelete('cascade');

            $table->foreign('pos_session_id', 'fk_pos_refunds_session')
                ->references('id')->on('pos_sessions')
                ->onDelete('cascade');

            $table->foreign('admin_user_id', 'fk_pos_refunds_admin')
                ->references('id')->on('admins')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_refunds');
    }
};
