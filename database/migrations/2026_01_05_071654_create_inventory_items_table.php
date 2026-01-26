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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
        
            // Relasi ke Referensi
            $table->foreignId('category_id')->constrained('inventory_categories');
            $table->foreignId('brand_id')->constrained('inventory_brands');
            
            // Relasi ke Lokasi (Nullable: kalau barang di gudang = null)
            $table->foreignId('inventory_id')->nullable()->constrained('inventory')->nullOnDelete();
            
            $table->string('serial_number')->unique();
            // $table->string('model_name')->nullable(); // e.g. "Odyssey G3"
            $table->date('purchase_date')->nullable();
            
            $table->enum('condition', ['good', 'maintenance', 'broken'])->default('good');
            
            // Kolom JSON Spesifikasi (Penyelamat hidupmu)
            $table->json('specifications')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
