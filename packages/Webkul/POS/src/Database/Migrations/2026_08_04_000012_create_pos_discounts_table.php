<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 255);
            $table->string('code', 50)->nullable();
            $table->enum('type', ['percentage', 'fixed', 'buy_x_get_y']);
            $table->decimal('value', 15, 4);
            $table->decimal('min_order_amount', 15, 4)->nullable();
            $table->decimal('max_discount_amount', 15, 4)->nullable();
            $table->enum('applies_to', ['order', 'item', 'shipping'])->default('order');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'uk_tenant_code');
            $table->index('tenant_id', 'idx_tenant');

            $table->foreign('tenant_id', 'fk_pos_discounts_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_discounts');
    }
};
