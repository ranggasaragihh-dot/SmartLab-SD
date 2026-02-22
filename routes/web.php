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
use App\Livewire\Admin\InventoryItemManager;
use App\Livewire\Admin\BookingHistory;
use App\Http\Controllers\InventoryScanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/display', PublicDisplay::class)->name('public.display');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('booking.index');
    })->name('dashboard');

    Route::get('/booking/{clusterId?}', BookingPage::class)->name('booking.index');
    
    Route::get('/scan/{qrToken}', [CheckInController::class, 'handleScan'])->name('scan.qr');
    Route::get('/inventory/scan/{asset_code}', [InventoryScanController::class, 'show'])->name('inventory.scan.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/inventory', AdminInventory::class)->name('inventory');
    Route::get('/clusters', ClusterManager::class)->name('clusters.index');

    Route::get('/inventaris', InventoryItemManager::class)->name('inventaris.index');

    Route::get('/denah-manager', DenahManager::class)->name('denah-manager');
    Route::get('/manajemen-user', UserManagement::class)->name('manajemen-user');

    Route::get('/booking', Dashboard::class)->name('booking.dashboard');
    Route::get('/booking/history', BookingHistory::class)->name('booking.history');
    Route::get('/booking/{clusterId}', Manager::class)->name('booking.manage');
});

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Route Auth bawaan dimatikan agar tidak konflik
// require __DIR__.'/auth.php';