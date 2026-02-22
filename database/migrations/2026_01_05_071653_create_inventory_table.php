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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cluster_id')->constrained('clusters')->cascadeOnDelete();
            $table->string('name')->unique();
            $table->string('qr_token')->unique();
            $table->enum('status', ['available', 'maintenance', 'broken'])->default('available');
            $table->boolean('is_bookable')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
