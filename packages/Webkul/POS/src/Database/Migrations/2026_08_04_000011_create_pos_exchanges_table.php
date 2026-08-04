<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_exchanges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('original_order_id');
            $table->unsignedBigInteger('new_order_id');
            $table->unsignedBigInteger('pos_session_id');
            $table->unsignedInteger('admin_user_id');
            $table->string('exchange_number', 50);
            $table->decimal('price_difference', 15, 4)->default(0.0000);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('exchange_number', 'uk_exchange_number');
            $table->index('tenant_id', 'idx_tenant');
            $table->index('original_order_id', 'idx_original');
            $table->index('new_order_id', 'idx_new');

            $table->foreign('tenant_id', 'fk_pos_exchanges_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('original_order_id', 'fk_pos_exchanges_original_order')
                ->references('id')->on('pos_orders')
                ->onDelete('cascade');

            $table->foreign('new_order_id', 'fk_pos_exchanges_new_order')
                ->references('id')->on('pos_orders')
                ->onDelete('cascade');

            $table->foreign('pos_session_id', 'fk_pos_exchanges_session')
                ->references('id')->on('pos_sessions')
                ->onDelete('cascade');

            $table->foreign('admin_user_id', 'fk_pos_exchanges_admin')
                ->references('id')->on('admins')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_exchanges');
    }
};
