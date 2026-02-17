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
            
            // Relasi User
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // 1. TAMBAHAN PENTING: CLUSTER ID
            // Kita wajib tahu booking ini terjadi di ruangan/lab mana
            // (Terutama jika inventory_id nya kosong/null)
            $table->foreignId('cluster_id')->constrained('clusters')->cascadeOnDelete();

            // 2. MODIFIKASI PENTING: INVENTORY ID (NULLABLE)
            // - Jika diisi angka = Booking PC/Alat spesifik (Public Lab)
            // - Jika NULL = Booking seluruh ruangan (Private Room)
            // Pastikan nama tabel referensinya benar ('inventory' atau 'inventories')
            $table->foreignId('inventory_id')
                  ->nullable() // <--- INI KUNCI STRATEGINYA
                  ->constrained('inventory') // Sesuaikan dengan nama tabel fisik Anda
                  ->cascadeOnDelete();
            
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('check_in_time')->nullable();
            
            // 3. UPDATE STATUS
            // Tambahkan 'pending' untuk logic approval ruangan jika diperlukan nanti
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