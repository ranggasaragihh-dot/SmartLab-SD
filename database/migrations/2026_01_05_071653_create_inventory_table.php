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
            
            $table->string('name')->unique(); // LAB1-PC01
            $table->string('qr_token')->unique(); // Token Rahasia
            
            // Status Operasional
            $table->enum('status', ['available', 'maintenance', 'broken'])->default('available');

            $table->boolean('is_bookable')->default(false);
            
            // Fitur "Dewa" Admin
            $table->enum('admin_override', ['none', 'force_open', 'force_close'])->default('none'); // NULL, 'FORCE_OPEN', 'FORCE_CLOSE'
            
            // Identitas untuk Agent Python
            $table->string('mac_address')->nullable();
            
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
