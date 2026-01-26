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
            $table->string('name'); // Contoh: "Lab Basis Data", "Lab Jaringan"
            // $table->string('location')->nullable(); // Contoh: "Gedung Bonaventura Lt. 3"
            $table->enum('type', ['public', 'private'])->default('public');
            $table->text('description')->nullable(); 
            $table->boolean('is_active')->default(true); // Biar bisa tutup lab sementara
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
