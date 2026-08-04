<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_employee_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedInteger('admin_user_id');
            $table->unsignedBigInteger('pos_employee_role_id');
            $table->unsignedBigInteger('pos_location_id')->nullable();
            $table->string('pin_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'admin_user_id'], 'uk_tenant_user');
            $table->index('tenant_id', 'idx_tenant');
            $table->index('pos_employee_role_id', 'idx_role');

            $table->foreign('tenant_id', 'fk_pos_employee_assignments_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');

            $table->foreign('admin_user_id', 'fk_pos_employee_assignments_admin')
                ->references('id')->on('admins')
                ->onDelete('cascade');

            $table->foreign('pos_employee_role_id', 'fk_pos_employee_assignments_role')
                ->references('id')->on('pos_employee_roles')
                ->onDelete('cascade');

            $table->foreign('pos_location_id', 'fk_pos_employee_assignments_location')
                ->references('id')->on('pos_locations')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_employee_assignments');
    }
};
