<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Booking;
use App\Models\Inventory;
use App\Models\User;
use Carbon\Carbon;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public $selectedBooking = null; 
    public $showDetailSidebar = false;

    public function openDetailSidebar($id)
    {
        // Peringatan Strategis: GUNAKAN EAGER LOADING!
        // Jangan cuma find($id), nanti aplikasi Anda lambat karena N+1 query problem
        // saat mengambil relasi user, inventory, dan cancelledBy di view.
        $this->selectedBooking = Booking::with([
            'user', 
            'inventory.cluster', 
        ])->find($id);

        // Jika data ditemukan, buka drawernya
        if ($this->selectedBooking) {
            $this->showDetailSidebar = true;
        } else {
            // Error handling dasar jika data tidak sengaja terhapus
            session()->flash('error', 'Data transaksi tidak ditemukan.');
        }
    }

    public function render()
    {
        // 1. Hitung Statistik Ringkas
        $stats = [
            'total_bookings' => Booking::count(),
            'today_bookings' => Booking::whereDate('start_time', Carbon::today())->count(),
            'active_now'     => Booking::where('status', 'active')->count(),
            'maintenance_pc' => Inventory::where('status', 'maintenance')->count(),
        ];

        // 2. Ambil 5 Booking Terbaru
        $recentBookings = Booking::with(['user', 'inventory'])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings
        ]);
    }
}