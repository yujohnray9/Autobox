<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('faculty')->after('name'); // admin, faculty, staff
            $table->string('department')->nullable()->after('role');
            $table->string('employee_id')->nullable()->unique()->after('department');
            $table->string('qr_token')->nullable()->unique()->after('employee_id');
            $table->boolean('is_active')->default(true)->after('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'department', 'employee_id', 'qr_token', 'is_active']);
        });
    }
};
