<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Cluster;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Dashboard extends Component
{

    #[Layout('layouts.admin')] 
    public function render()
    {
        $clusters = Cluster::withCount([
            'inventories',
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