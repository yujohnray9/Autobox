<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->string('previous_hash', 64)->nullable()->after('ip_address');
            $table->string('record_hash', 64)->nullable()->index()->after('previous_hash');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('login_otp_code', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropColumn(['record_hash', 'previous_hash']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('login_otp_code', 10)->nullable()->change();
        });
    }
};
