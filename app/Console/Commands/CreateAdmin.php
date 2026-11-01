<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin
                            {--nomor-induk= : Nomor induk untuk login}
                            {--name= : Nama admin}
                            {--email= : Email (opsional)}';

    protected $description = 'Buat akun admin (atau jadikan user yang sudah ada sebagai admin)';

    public function handle(): int
    {
        $nomorInduk = $this->option('nomor-induk') ?: $this->ask('Nomor induk (dipakai untuk login)');
        $user = User::where('nomor_induk', $nomorInduk)->first();

        if (!$user) {
            $name = $this->option('name') ?: $this->ask('Nama lengkap');
            $email = $this->option('email') ?? $this->ask('Email (kosongkan jika tidak ada)', '');
            $password = $this->secret('Password (minimal 8 karakter)');
            $confirm = $this->secret('Ulangi password');

            $validator = Validator::make(
                compact('nomorInduk', 'name', 'email', 'password') + ['password_confirmation' => $confirm],
                [
                    'nomorInduk' => 'required|string|max:20',
                    'name' => 'required|string|min:3',
                    'email' => 'nullable|email|unique:users,email',
                    'password' => 'required|string|min:8|confirmed',
                ]
            );
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->error($error);
                }
                return self::FAILURE;
            }

            $user = User::create([
                'nomor_induk' => $nomorInduk,
                'name' => $name,
                'email' => $email ?: null,
                'password' => $password,
            ]);
        }

        $adminRole = Role::firstOrCreate(['role' => 'admin']);
        $user->roles()->sync([$adminRole->id]);

        $this->info("Akun {$user->name} ({$user->nomor_induk}) sekarang admin.");

        return self::SUCCESS;
    }
}
