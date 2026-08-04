<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedInteger('admin_user_id')->nullable();
            $table->string('event_type', 100);
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['tenant_id', 'entity_type', 'entity_id'], 'idx_tenant_entity');
            $table->index('event_type', 'idx_event_type');

            $table->foreign('tenant_id', 'fk_pos_audit_logs_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_audit_logs');
    }
};
