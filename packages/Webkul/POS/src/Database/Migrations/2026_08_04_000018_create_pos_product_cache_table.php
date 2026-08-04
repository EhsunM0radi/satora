<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_product_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('pos_terminal_id');
            $table->unsignedInteger('product_id');
            $table->json('cached_data');
            $table->timestamp('last_synced_at');
            $table->timestamps();

            $table->unique(['pos_terminal_id', 'product_id'], 'uk_terminal_product');
            $table->index('tenant_id', 'idx_tenant');

            $table->foreign('tenant_id', 'fk_pos_product_cache_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('pos_terminal_id', 'fk_pos_product_cache_terminal')
                ->references('id')->on('pos_terminals')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_product_cache');
    }
};
