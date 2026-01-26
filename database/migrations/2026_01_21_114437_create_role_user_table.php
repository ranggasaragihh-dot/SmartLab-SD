<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            // Foreign Key ke Users
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Foreign Key ke Roles
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            
            // Mencegah duplikasi data
            $table->unique(['user_id', 'role_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
