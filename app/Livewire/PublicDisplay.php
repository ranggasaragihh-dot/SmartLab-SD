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
        // 1. Ambil Data Cluster beserta Inventory & Booking Aktif
        $clusters = Cluster::with(['inventories' => function($q) use ($now) {
            $q->with(['bookings' => function($b) use ($now) {
                // PERBAIKAN: Gunakan whereIn untuk multiple status
                $b->whereIn('status', ['booked', 'active'])
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
                if ($booking && (is_null($booking->inventory_id))) {
                    // Logic ini bisa Anda kustomisasi: jika salah satu unit aktif, 
                    // apakah dianggap 1 cluster sedang dipakai?
                    $activeEvent = $booking;
                    break;
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