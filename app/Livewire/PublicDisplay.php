<?php

namespace App\Livewire;

use App\Models\Cluster;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PublicDisplay extends Component
{
    #[Layout('layouts.blank')]
    public function render()
    {
        $now = Carbon::now();

        // 1. Ambil Data Cluster
        $clusters = Cluster::with([
            'inventories' => function($q) use ($now) {
                $q->with(['bookings' => function($b) use ($now) {
                    $b->whereIn('status', ['booked', 'active'])
                      ->where('start_time', '<=', $now)
                      ->where('end_time', '>', $now);
                }]);
            },
            'bookings' => function($b) use ($now) {
                $b->whereNull('inventory_id')
                  ->whereIn('status', ['booked', 'active'])
                  ->where('start_time', '<=', $now)
                  ->where('end_time', '>', $now);
            }
        ])->get();

        // 2. Logic Tambahan & Global Event Check
        $globalEvent = null;

        $clusters->map(function ($cluster) use (&$globalEvent) {
            // A. Sort PC
            $sortedInventory = $cluster->inventories->sortBy('name', SORT_NATURAL)->values();
            $cluster->setRelation('inventory', $sortedInventory);

            // B. Cek Booking Ruangan per Cluster
            $cluster->active_room_booking = $cluster->bookings->first();

            // C. LOGIKA PENGUNCIAN GLOBAL
            // Jika belum ada global event yg terdeteksi, dan cluster ini punya booking ruangan,
            // Maka jadikan ini sebagai Global Event (Kunci Semua Layar)
            if (!$globalEvent && $cluster->active_room_booking) {
                $globalEvent = $cluster->active_room_booking;
            }

            return $cluster;
        });

        return view('livewire.public-display', [
            'clusters' => $clusters,
            'globalEvent' => $globalEvent // Kirim variabel ini ke View
        ]);
    }
}