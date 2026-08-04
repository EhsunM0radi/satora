<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_hardware_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_terminal_id');
            $table->enum('device_type', ['barcode_scanner', 'receipt_printer', 'cash_drawer', 'customer_display', 'weight_scale']);
            $table->string('event_type', 50);
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('tenant_id', 'idx_tenant');
            $table->index(['pos_terminal_id', 'device_type'], 'idx_terminal_device');

            $table->foreign('tenant_id', 'fk_pos_hardware_events_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_terminal_id', 'fk_pos_hardware_events_terminal')
                ->references('id')->on('pos_terminals')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_hardware_events');
    }
};
