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
            $table->string('name');
            $table->string('denah_image')->nullable();
            $table->integer('delay_time')->default(15);
            $table->integer('min_duration_minutes')->default(30);
            $table->integer('max_duration_minutes')->default(120);
            $table->enum('type', ['public', 'private'])->default('public');
            $table->text('description')->nullable(); 
            $table->boolean('is_active')->default(true);
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