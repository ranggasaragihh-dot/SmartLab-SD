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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cluster_id')->constrained('clusters')->cascadeOnDelete();
            $table->foreignId('inventory_id')
                  ->nullable()
                  ->constrained('inventory')
                  ->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('check_in_time')->nullable();
            $table->enum('status', ['booked', 'active', 'completed', 'cancelled'])->default('booked');
            $table->string('purpose')->nullable();
            $table->string('cancel_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};