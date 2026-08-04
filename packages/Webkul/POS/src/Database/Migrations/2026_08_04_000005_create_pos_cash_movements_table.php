<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_cash_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_session_id');
            $table->unsignedBigInteger('pos_cash_register_id');
            $table->unsignedInteger('admin_user_id');
            $table->enum('type', ['opening', 'closing', 'cash_in', 'cash_out', 'sale', 'refund', 'expense', 'deposit']);
            $table->decimal('amount', 15, 4);
            $table->decimal('balance_after', 15, 4);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_session_id', 'idx_session');
            $table->index('pos_cash_register_id', 'idx_register');
            $table->index('type', 'idx_type');
            $table->index(['reference_type', 'reference_id'], 'idx_reference');

            $table->foreign('tenant_id', 'fk_pos_cash_movements_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_session_id', 'fk_pos_cash_movements_session')
                ->references('id')->on('pos_sessions')
                ->onDelete('cascade');

            $table->foreign('pos_cash_register_id', 'fk_pos_cash_movements_register')
                ->references('id')->on('pos_cash_registers')
                ->onDelete('cascade');

            $table->foreign('admin_user_id', 'fk_pos_cash_movements_admin')
                ->references('id')->on('admins')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_cash_movements');
    }
};
