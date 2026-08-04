<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_terminals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_location_id');
            $table->string('name', 255);
            $table->string('code', 50);
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->json('hardware_profile')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code'], 'uk_tenant_code');
            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_location_id', 'idx_location');

            $table->foreign('tenant_id', 'fk_pos_terminals_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_location_id', 'fk_pos_terminals_location')
                ->references('id')->on('pos_locations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_terminals');
    }
};
