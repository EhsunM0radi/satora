<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_offline_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_terminal_id');
            $table->string('action', 50);
            $table->json('payload');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'conflict'])->default('pending');
            $table->string('local_id', 100)->nullable();
            $table->unsignedBigInteger('server_id')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id', 'idx_tenant');
            $table->index(['pos_terminal_id', 'status'], 'idx_terminal_status');
            $table->index('local_id', 'idx_local_id');

            $table->foreign('tenant_id', 'fk_pos_offline_queues_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_terminal_id', 'fk_pos_offline_queues_terminal')
                ->references('id')->on('pos_terminals')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_offline_queues');
    }
};
