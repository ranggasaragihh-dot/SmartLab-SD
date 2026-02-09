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
        Schema::create('clusters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "Lab Basis Data", "Meeting Room A"
            $table->string('denah_image')->nullable();
            
            // ATURAN WAKTU (PENTING)
            $table->integer('delay_time')->default(15); // Toleransi telat check-in (menit)
            $table->integer('min_duration_minutes')->default(30);
            $table->integer('max_duration_minutes')->default(120); // Batas maksimal booking (default 2 jam). Null = Unlimited.
            
            // TIPE RUANGAN (PENTING)
            // Public = Booking per PC (Inventory ID wajib)
            // Private = Booking satu ruangan (Inventory ID null)
            $table->enum('type', ['public', 'private'])->default('public');
            
            $table->text('description')->nullable(); 
            $table->boolean('is_active')->default(true); // Switch ON/OFF ruangan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clusters');
    }
};