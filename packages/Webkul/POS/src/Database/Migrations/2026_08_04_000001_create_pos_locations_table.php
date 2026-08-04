<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name', 255);
            $table->string('code', 50);
            $table->enum('type', ['store', 'warehouse', 'popup', 'mobile'])->default('store');
            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->default('IR');
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('timezone', 50)->default('Asia/Tehran');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code'], 'uk_tenant_code');
            $table->index('tenant_id', 'idx_tenant');

            $table->foreign('tenant_id', 'fk_pos_locations_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_locations');
    }
};
