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
        // 1. Ambil Data Cluster beserta Inventory & Booking Aktif
        $clusters = Cluster::with(['inventories' => function($q) {
            $q->with(['bookings' => function($b) {
                // Filter: Hanya ambil booking yang SEDANG BERLANGSUNG
                $now = Carbon::now();
                $b->where('status', 'booked')
                ->where('start_time', '<=', $now)
                ->where('end_time', '>', $now);
            }]);
        }])->get();

        // 2. Logic Tambahan (Sorting & Event Check)
        foreach($clusters as $cluster) {
            
            // A. PERBAIKAN URUTAN (Natural Sort)
            // Ini solusi agar PC-1, PC-2, PC-10 urut benar.
            // Kita urutkan collection inventory menggunakan PHP, bukan SQL.
            $sortedInventory = $cluster->inventories->sortBy('name', SORT_NATURAL)->values();

            // B. DETEKSI EVENT (Mode 1 Ruangan)
            $activeEvent = null;
            
            foreach($sortedInventory as $unit) {
                // Ambil booking pertama (karena sudah difilter di query atas, ini pasti booking aktif)
                $booking = $unit->bookings->first();
                
                // Cek apakah tipe bookingnya 'event' (bukan booking satuan mahasiswa)
                if ($booking && $booking->type === 'event') {
                    $activeEvent = $booking;
                    break; // Jika ketemu satu, berarti 1 cluster itu sedang dipakai event. Stop loop.
                }
            }

            // C. SIMPAN KEMBALI DATA YANG SUDAH DIOLAH
            // Timpa relasi 'inventory' dengan yang sudah diurutkan agar di View rapi
            $cluster->setRelation('inventory', $sortedInventory);
            
            // Simpan info event ke properti baru
            $cluster->currentEvent = $activeEvent;
        }

        return view('livewire.public-display', [
            'clusters' => $clusters
        ]);
    }
}