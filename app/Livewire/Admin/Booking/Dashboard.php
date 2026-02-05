<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Cluster;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Dashboard extends Component
{
    // Menggunakan Layout Admin yang sudah Anda buat
    #[Layout('layouts.admin')] 
    public function render()
    {
        // Mengambil data Cluster dengan perhitungan statistik otomatis
        // Pastikan relasi 'units' ada di model Cluster
        $clusters = Cluster::withCount([
            'inventories', // Menghitung total unit (units_count)
            'inventories as available_count' => function ($query) {
                $query->where('status', 'available');
            },
            'bookings as booked_count' => function ($query) {
                $query->where('bookings.status', 'booked');
            },
            'inventories as maintenance_count' => function ($query) {
                $query->where('status', 'maintenance');
            },
            'inventories as broken_count' => function ($query) {
                $query->where('status', 'broken');
            }
        ])->get();

        return view('livewire.admin.dashboard-booking', [
            'clusters' => $clusters
        ]);
    }
}