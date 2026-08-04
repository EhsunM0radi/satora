<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_terminal_id');
            $table->unsignedInteger('admin_user_id');
            $table->string('session_number', 50);
            $table->enum('status', ['open', 'closing', 'closed', 'suspended'])->default('open');
            $table->decimal('opening_balance', 15, 4)->default(0.0000);
            $table->decimal('closing_balance', 15, 4)->nullable();
            $table->decimal('expected_balance', 15, 4)->nullable();
            $table->decimal('difference', 15, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique('session_number', 'uk_session_number');
            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_terminal_id', 'idx_terminal');
            $table->index('status', 'idx_status');

            $table->foreign('tenant_id', 'fk_pos_sessions_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_terminal_id', 'fk_pos_sessions_terminal')
                ->references('id')->on('pos_terminals')
                ->onDelete('cascade');

            $table->foreign('admin_user_id', 'fk_pos_sessions_admin')
                ->references('id')->on('admins')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
