<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
        
            // 1. RELASI (Tetap)
            $table->foreignId('category_id')->constrained('inventory_categories'); // Excel: Kategori
            $table->foreignId('brand_id')->constrained('inventory_brands');       // Excel: Merk
            $table->string('item_name')->nullable();
            // Relasi ke Meja/Slot (PENTING UNTUK SISTEM 1 QR)
            // Jika terisi = Barang sedang dipakai di Meja tersebut
            // Jika null   = Barang di gudang / rusak
            $table->foreignId('inventory_id')
                  ->nullable()
                  ->constrained('inventory')
                  ->nullOnDelete();
            
            // 2. IDENTITAS UNIK BARANG
            // Excel: "Kode Asset" (Internal Kampus/Kantor)
            $table->string('asset_code')->unique()->nullable(); 
            
            // Excel: Serial Number (Bawaan Pabrik, opsional jika beda dengan Kode Asset)
            $table->string('serial_number')->nullable(); 

            // 3. DATA ADMINISTRASI (TAMBAHAN DARI EXCEL)
            // Excel: "Aktiva" (Misal: Aset Tetap, Aset Lancar)
            $table->string('asset_type')->nullable(); 
            
            // Excel: "Harga Beli" (Gunakan Decimal untuk uang presisi)
            $table->decimal('purchase_price', 15, 2)->nullable(); 
            
            // Excel: "No Dokumen" (SPK/Faktur/BAST)
            $table->string('document_number')->nullable(); 
            
            // Excel: "Unit" (Pemilik barang, misal: Prodi IF, Lab A)
            $table->string('owner_unit')->nullable();

            // 4. KONDISI & SPESIFIKASI
            // Excel: "Tanggal Terima"
            $table->date('purchase_date')->nullable();
            
            // Excel: "Kondisi Barang"
            $table->enum('condition', ['good', 'maintenance', 'broken'])->default('good');
            
            // Excel: "Spesifikasi" (Disimpan dalam JSON agar fleksibel untuk filter)
            // Contoh isi: {"cpu": "i5", "ram": "8gb", "storage": "ssd 256"}
            $table->json('specifications')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};