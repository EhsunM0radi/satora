<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_cash_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_terminal_id');
            $table->unsignedBigInteger('pos_session_id');
            $table->string('name', 100)->default('Main Register');
            $table->enum('type', ['cash', 'card_terminal', 'mixed'])->default('cash');
            $table->decimal('current_balance', 15, 4)->default(0.0000);
            $table->string('currency', 3)->default('IRR');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_terminal_id', 'idx_terminal');
            $table->index('pos_session_id', 'idx_session');

            $table->foreign('tenant_id', 'fk_pos_cash_registers_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_terminal_id', 'fk_pos_cash_registers_terminal')
                ->references('id')->on('pos_terminals')
                ->onDelete('cascade');

            $table->foreign('pos_session_id', 'fk_pos_cash_registers_session')
                ->references('id')->on('pos_sessions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_cash_registers');
    }
};
