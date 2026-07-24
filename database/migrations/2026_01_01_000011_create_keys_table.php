<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keys', function (Blueprint $table) {
            $table->id();
            $table->string('key_name');
            $table->string('room_name');
            $table->text('description')->nullable();
            $table->integer('slot_number')->unique();
            $table->enum('status', ['available', 'borrowed', 'missing'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keys');
    }
};
