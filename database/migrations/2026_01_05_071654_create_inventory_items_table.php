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
            $table->foreignId('category_id')->constrained('inventory_categories');
            $table->foreignId('brand_id')->constrained('inventory_brands');
            $table->string('item_name')->nullable();
            $table->foreignId('inventory_id')
                  ->nullable()
                  ->constrained('inventory')
                  ->nullOnDelete();
            $table->string('asset_code')->unique()->nullable(); 
            $table->string('serial_number')->nullable();
            $table->string('asset_type')->nullable(); 
            $table->decimal('purchase_price', 15, 2)->nullable(); 
            $table->string('document_number')->nullable(); 
            $table->string('owner_unit')->nullable();
            $table->date('purchase_date')->nullable();
            $table->enum('condition', ['good', 'maintenance', 'broken'])->default('good');
            $table->json('specifications')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};