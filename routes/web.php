<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CheckInController;

// Livewire Components
use App\Livewire\Pages\Auth\Login;
use App\Livewire\BookingPage;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Inventory as AdminInventory;
use App\Livewire\Admin\LabManager;
use App\Livewire\Admin\ClusterManager;
use App\Livewire\Admin\DenahManager;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\Booking\Dashboard;
use App\Livewire\Admin\Booking\Manager;
use App\Livewire\PublicDisplay;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/display', PublicDisplay::class)->name('public.display');

// 1. ROOT URL: Selalu lempar ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. GUEST: Hanya bisa diakses kalau BELUM login
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// 3. AUTHENTICATED USER (Mahasiswa & Admin bisa masuk sini)
Route::middleware('auth')->group(function () {
    
    // === JARINGS PENGAMAN (THE BOUNCER) ===
    // Ini PENTING! Jika ada yang nyasar ke '/dashboard', kita arahkan ulang sesuai role.
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('booking.index');
    })->name('dashboard');

    // === FITUR UTAMA MAHASISWA ===
    Route::get('/booking/{clusterId?}', BookingPage::class)->name('booking.index');
    
    // === FITUR LAINNYA ===
    Route::get('/scan/{qrToken}', [CheckInController::class, 'handleScan'])->name('scan.qr');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 4. ADMIN ONLY: Hanya Admin yang bisa akses ini
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/inventory', AdminInventory::class)->name('inventory');
    Route::get('/lab/{clusterId}', LabManager::class)->name('lab.manager');
    Route::get('/clusters', ClusterManager::class)->name('clusters.index');

    // 2. ROUTE UNTUK MENGELOLA ISI DALAM LAB (YANG SUDAH ADA SEBELUMNYA)
    // Route::get('/lab/{clusterId}', \App\Livewire\Admin\LabManager::class)->name('lab.manage');
    Route::get('/denah-manager', DenahManager::class)->name('denah-manager');
    Route::get('/manajemen-user', UserManagement::class)->name('manajemen-user');

    Route::get('/booking', Dashboard::class)->name('booking.dashboard');
    // 2. Manager (Detail Lab dengan Dropdown)
    Route::get('/booking/{clusterId}', Manager::class)->name('booking.manage');
});

// 5. LOGOUT: Wajib Method POST
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Route Auth bawaan dimatikan agar tidak konflik
// require __DIR__.'/auth.php';