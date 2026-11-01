<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

STEP BY STEP
Langkah-Langkah pertama kali run project

1. composer install
2. copy .env.example .env
3. php artisan key:generate
4. Turn On XAMPP
5. php artisan migrate
6. npm install ( if error "2 high severity vulnerabilities" run "npm audit fix")
7. php artisan tinker
8.
    App\Models\Role::firstOrCreate(['role' => 'admin']);
    App\Models\Role::firstOrCreate(['role' => 'lecturer']);
    App\Models\Role::firstOrCreate(['role' => 'student']);
9.
    App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@smartlab.com',
        'password' => bcrypt('admin123'),
        'nomor_induk' => 'A001',
    ])->roles()->attach(1);
10. Untuk pengembangan lokal di komputer, jalankan `php artisan serve` dan `npm run dev`.
11. Untuk membuka aplikasi dari HP, laptop lain, atau melalui tunnel, jalankan `composer dev-lan`. Perintah ini membangun CSS/JS, menghapus penanda Vite development yang dapat membuat perangkat mencari asset di `localhost:5173`, lalu menjalankan aplikasi di `0.0.0.0:8000`. Arahkan tunnel ke port `8000`.
