<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Booking;
use App\Models\Inventory;
use App\Services\BookingService;
use Carbon\Carbon;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    use WithPagination;

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
        app(BookingService::class)->synchronizeExpiredBookings();

        $stats = [
            'total_bookings' => Booking::count(),
            'today_bookings' => Booking::whereDate('start_time', Carbon::today())->count(),
            'active_now'     => Booking::where('status', 'active')->count(),
            'maintenance_pc' => Inventory::where('status', 'maintenance')->count(),
        ];

        $recentBookings = Booking::with(['user', 'inventory'])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings
        ]);
    }
}