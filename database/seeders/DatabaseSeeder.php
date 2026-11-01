<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Data wajib agar aplikasi bisa dipakai. Aman dijalankan berulang kali.
     * Akun admin pertama dibuat dengan: php artisan app:create-admin
     */
    public function run(): void
    {
        foreach (['admin', 'student', 'lecturer'] as $role) {
            Role::firstOrCreate(['role' => $role]);
        }
    }
}
