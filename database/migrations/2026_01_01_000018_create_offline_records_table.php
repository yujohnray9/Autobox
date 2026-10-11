<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staging table for events recorded by the Raspberry Pi while it was offline.
     * Records stay "pending" until an admin reviews them and clicks "Sync".
     */
    public function up(): void
    {
        Schema::create('offline_records', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 64)->unique();              // Pi-generated id (prevents duplicate uploads)
            $table->string('type', 32);                        // transaction | access_log
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('slot_number')->nullable();
            $table->string('action', 64)->nullable();
            $table->string('result', 32)->nullable();
            $table->string('reason')->nullable();
            $table->json('payload');
            $table->timestamp('occurred_at')->nullable();
            $table->enum('status', ['pending', 'synced', 'skipped'])->default('pending');
            $table->string('sync_note')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->foreignId('synced_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_records');
    }
};
