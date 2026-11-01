<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sesuai DPPL (Tabel 5): unit PC baru defaultnya boleh dibooking.
     * Data yang sudah ada tidak diubah.
     */
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->boolean('is_bookable')->default(true)->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->boolean('is_bookable')->default(false)->change();
        });
    }
};
