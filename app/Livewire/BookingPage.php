<?php

namespace App\Livewire;

use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Booking;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

#[Layout('layouts.booking')]
class BookingPage extends Component
{
    // --- STATE UTAMA ---
    public $selectedClusterId = null;
    public $cluster; 

    // --- FILTER & FORM ---
    public $filterDate;
    public $start_time;
    public $end_time;
    
    // --- DATA SELECTION ---
    public $selectedInventoryId = null; 
    public $selectedInventoryName = '';
    
    // --- MODAL STATE ---
    public $showBookModal = false;
    public $showCancelModal = false;
    public $showFinishModal = false;
    
    // --- EDIT/CANCEL STATE ---
    public $selectedBookingId = null;
    public $selectedBooking;
    public $bookingStatus = null;

    public function mount($clusterId = null)
    {
        $this->filterDate = Carbon::today()->format('Y-m-d');

        if ($clusterId) {
            $this->cluster = Cluster::findOrFail($clusterId);
        } else {
            // Default ke cluster aktif pertama
            $this->cluster = Cluster::where('is_active', true)->first();
        }

        if ($this->cluster) {
            $this->selectedClusterId = $this->cluster->id;
        }
    }

    // Listener saat dropdown ganti
    public function updatedSelectedClusterId($value)
    {
        $this->cluster = Cluster::find($value);
        $this->reset(['selectedInventoryId', 'selectedInventoryName', 'start_time', 'end_time']);
    }

    private function resetModals()
    {
        // Matikan semua saklar modal
        $this->showBookModal = false;
        $this->showCancelModal = false;
        $this->showFinishModal = false;
        
        // Kosongkan data container
        $this->selectedBookingId = null;
        $this->selectedBooking = null;
        $this->selectedInventoryId = null;
        $this->selectedInventoryName = null;
        
        $this->resetErrorBag();
    }

    // =================================================================
    // 1. FUNGSI INTERAKSI (Hanya Booking Satuan)
    // =================================================================

    /**
     * Logic saat user klik kotak PC/Meja.
     */
    public function clickSeat($inventoryId)
    {
        $this->resetModals(); // Bersihkan state dulu

        $inventory = Inventory::find($inventoryId);

        // Validasi Fisik: Jika tidak ada atau Rusak/Maintenance, stop.
        if (!$inventory || in_array($inventory->status, ['broken', 'maintenance'])) {
            return; 
        }

        // Set Data Inventory Dasar
        $this->selectedInventoryId = $inventory->id;
        $this->selectedInventoryName = $inventory->name;

        // Cek Booking Aktif/Mendatang di Unit ini
        $activeBooking = Booking::where('inventory_id', $inventoryId)
            ->where('user_id', Auth::id()) // Cek milik user sendiri
            ->whereNotIn('status', ['cancelled', 'rejected', 'completed'])
            ->where('end_time', '>', Carbon::now()) // Yang belum berakhir
            ->orderBy('start_time', 'asc')
            ->first();

        // --- SKENARIO A: SAYA PUNYA JADWAL DI SINI ---
        if ($activeBooking) {
            
            $this->selectedBookingId = $activeBooking->id;
            $this->selectedBooking = $activeBooking;

            // LOGIKA WAKTU (Time Gate)
            $now = Carbon::now();
            $startTime = Carbon::parse($activeBooking->start_time);

            // ATURAN TEGAS:
            // 1. Jika Status ACTIVE (Sudah Check-in) -> PASTI FINISH (Tidak boleh cancel)
            // 2. Jika Waktu SUDAH MULAI (Meski belum check-in) -> PASTI FINISH
            if ($activeBooking->status === 'active' || $now->greaterThanOrEqualTo($startTime)) {
                $this->showFinishModal = true;
            } 
            // 3. Jika Masih Status BOOKED DAN Waktu BELUM MULAI -> BOLEH CANCEL
            else {
                $this->showCancelModal = true;
            }

        } 
        // --- SKENARIO B: UNIT KOSONG/AVAILABLE (Atau punya orang lain) ---
        else {
            // Cek dulu apakah dipakai orang lain?
            $isBookedByOther = Booking::where('inventory_id', $inventoryId)
                ->whereNotIn('status', ['cancelled', 'rejected', 'completed'])
                ->where('end_time', '>', Carbon::now())
                ->exists();

            if ($isBookedByOther) {
                // Opsional: Beri tahu user unit ini dipakai orang
                // $this->dispatch('notify', ...);
                return; 
            }

            // Jika benar-benar kosong, buka form booking baru
            $this->reset(['start_time', 'end_time']);
            $this->showBookModal = true;
        }
    }

    // CATATAN: Fungsi clickRoom() SUDAH DIHAPUS karena pindah ke Admin.

    // =================================================================
    // 2. FUNGSI TRANSAKSI
    // =================================================================

    public function createBooking()
    {
        // 1. Validasi Input
        $this->validate([
            'start_time' => 'required|date|after:now',
            'end_time'   => 'required|date|after:start_time',
        ]);

        $start = Carbon::parse($this->start_time);
        $end   = Carbon::parse($this->end_time);
        $diffMinutes = $start->diffInMinutes($end);

        // 2. Cek Aturan Durasi Cluster
        if ($this->cluster->max_duration_minutes && $diffMinutes > $this->cluster->max_duration_minutes) {
            $jam = $this->cluster->max_duration_minutes / 60;
            $this->addError('end_time', "Maksimal durasi main di sini adalah {$jam} jam.");
            return;
        }

        // 3. Cek Ketersediaan (PENTING: Cek juga apakah Admin membooking Full Ruangan)
        if (!$this->isSlotAvailable($start, $end)) {
            $this->addError('start_time', 'Slot tidak tersedia (Mungkin ruangan dibooking Admin/Event).');
            return;
        }

        // 4. Simpan Booking Satuan
        try {
            Booking::create([
                'user_id'      => Auth::id(),
                'cluster_id'   => $this->cluster->id,
                'inventory_id' => $this->selectedInventoryId, // Pasti ada isinya (bukan null)
                'start_time'   => $this->start_time,
                'end_time'     => $this->end_time,
                'status'       => 'booked',
            ]);

            session()->flash('success', 'Booking berhasil!');
            $this->showBookModal = false;
            $this->reset(['start_time', 'end_time']);

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function cancelBooking()
    {
        $booking = Booking::find($this->selectedBookingId);

        // 1. Validasi Keberadaan & Kepemilikan
        if (!$booking || $booking->user_id !== Auth::id()) {
            return;
        }

        // 2. SECURITY CHECK: Validasi Waktu Server
        $now = Carbon::now();
        $startTime = Carbon::parse($booking->start_time);

        // Jika user memaksa cancel padahal jam sudah lewat (atau sudah check-in)
        if ($booking->status === 'active' || $now->greaterThanOrEqualTo($startTime)) {
            
            // Tolak Aksi
            session()->flash('error', 'Gagal! Sesi sudah berjalan. Silakan selesaikan sesi (Finish).');
            
            // Redirect Pintar: Tutup modal cancel, paksa buka modal finish
            $this->showCancelModal = false;
            $this->showFinishModal = true;
            
            return; // BERHENTI DISINI
        }

        // 3. Eksekusi Pembatalan (Jika lolos validasi)
        $booking->update(['status' => 'cancelled']);
        
        $this->resetModals(); // Tutup semua modal
        session()->flash('success', 'Booking berhasil dibatalkan.');
    }

    public function finishBooking()
    {
        $booking = Booking::find($this->selectedBookingId);
        
        // 1. Validasi Keberadaan & Kepemilikan
        if (!$booking || $booking->user_id !== Auth::id()) {
            return;
        }

        // 2. Eksekusi Selesai
        // Kita tidak perlu cek waktu start disini, karena checkout dini (pulang cepat) itu boleh.
        $booking->update(['status' => 'completed']);
        
        $this->resetModals(); // Tutup semua modal
        session()->flash('success', 'Terima kasih, sesi Anda telah selesai.');
    }

    public function processCheckIn()
    {
        if ($this->selectedBookingId) {
            $booking = Booking::find($this->selectedBookingId);
            $booking->update([
                'status' => 'active',
                'check_in_time' => Carbon::now()
            ]);
            $this->bookingStatus = 'active';
            session()->flash('success', 'Check-in Berhasil!');
        }
    }

    // =================================================================
    // 3. HELPER LOGIC (ANTI BENTROK USER VS ADMIN)
    // =================================================================

    private function isSlotAvailable($start, $end)
    {
        // Logika: User mau booking PC X.
        // Gagal Jika:
        // 1. PC X itu sendiri sudah dibooking orang lain.
        // 2. ATAU Ruangan Lab ini sudah dibooking FULL oleh Admin (inventory_id NULL).
        
        return ! Booking::where('cluster_id', $this->cluster->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                // Cek Spesifik PC atau Cek Global Ruangan
                $query->where('inventory_id', $this->selectedInventoryId)
                      ->orWhereNull('inventory_id'); 
            })
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($sub) use ($start, $end) {
                      $sub->where('start_time', '<', $start)
                          ->where('end_time', '>', $end);
                  });
            })
            ->exists();
    }

    // =================================================================
    // 4. RENDER
    // =================================================================
    public function render()
    {
        if (!$this->cluster) {
            return view('livewire.booking-page', [
                'allowedClusters'     => [],
                'inventories'         => collect(),
                'bookingsMap'         => [],
                'isRoomLockedByAdmin' => false,
                'currentUserId'       => null,
                'currentClusterName'  => '',
                'cluster'             => null,
            ]);
        }

        // Query Cluster (Hanya Public untuk Mahasiswa, atau Semua jika diperlukan)
        $allowedClusters = Cluster::where('is_active', true)
            ->when(Auth::user()->role === 'student', function($q) {
                $q->where('type', 'public'); // Mahasiswa cuma bisa liat Lab Public
            })
            ->orderBy('name')
            ->get();

        // Ambil Inventory
        $inventories = Inventory::where('cluster_id', $this->cluster->id)
            ->where('status', '!=', 'broken')
            ->orderBy('name', 'asc')
            ->get();

        // Mapping Booking untuk Denah
        // Kita perlu tahu PC mana yang booked, DAN apakah ruangan ini sedang di-lock admin?
        $bookingsMap = [];
        $isRoomLockedByAdmin = false;

        $todayBookings = Booking::where('cluster_id', $this->cluster->id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_time', $this->filterDate)
            ->where('end_time', '>', Carbon::now())
            ->get();
        
        foreach($todayBookings as $b) {
            if ($b->inventory_id) {
                // Booking Satuan Biasa
                $bookingsMap[$b->inventory_id] = $b->user_id;
            } else {
                // ADA BOOKING RUANGAN (Oleh Admin)
                // Jika ini terjadi, semua PC harus dianggap 'booked' secara visual
                $isRoomLockedByAdmin = true;
            }
        }

        return view('livewire.booking-page', [
            'allowedClusters' => $allowedClusters,
            'inventories'     => $inventories,
            'bookingsMap'     => $bookingsMap,
            'isRoomLockedByAdmin' => $isRoomLockedByAdmin,
            'currentUserId'   => Auth::id(),
            'currentClusterName' => $this->cluster->name,
            'cluster'         => $this->cluster,
        ]);
    }
}