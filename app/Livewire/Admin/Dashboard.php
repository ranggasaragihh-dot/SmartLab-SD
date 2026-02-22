<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Booking;
use App\Models\Inventory;
use Carbon\Carbon;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public $selectedBooking = null; 
    public $showDetailSidebar = false;

    public function openDetailSidebar($id)
    {
        $this->selectedBooking = Booking::with([
            'user', 
            'inventory.cluster', 
        ])->find($id);

        if ($this->selectedBooking) {
            $this->showDetailSidebar = true;
        } else {
            session()->flash('error', 'Data transaksi tidak ditemukan.');
        }
    }

    public function render()
    {
        $stats = [
            'total_bookings' => Booking::count(),
            'today_bookings' => Booking::whereDate('start_time', Carbon::today())->count(),
            'active_now'     => Booking::where('status', 'active')->count(),
            'maintenance_pc' => Inventory::where('status', 'maintenance')->count(),
        ];

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