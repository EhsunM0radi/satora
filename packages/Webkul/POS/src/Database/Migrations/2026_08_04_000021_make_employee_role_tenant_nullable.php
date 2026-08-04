<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing unique constraint that requires tenant_id
        Schema::table('pos_employee_roles', function (Blueprint $table) {
            $table->dropForeign('fk_pos_employee_roles_tenant');
            $table->dropUnique('uk_tenant_code');
        });

        Schema::table('pos_employee_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
            $table->unique(['tenant_id', 'code'], 'uk_tenant_code');
            $table->foreign('tenant_id', 'fk_pos_employee_roles_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');
        });

        // Same for assignments
        Schema::table('pos_employee_assignments', function (Blueprint $table) {
            $table->dropForeign('fk_pos_employee_assignments_tenant');
            $table->dropUnique('uk_tenant_user');
        });

        Schema::table('pos_employee_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
            $table->unique(['tenant_id', 'admin_user_id'], 'uk_tenant_user');
            $table->foreign('tenant_id', 'fk_pos_employee_assignments_tenant')
                ->references('id')->on('tenants')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // No reverse
    }
};
