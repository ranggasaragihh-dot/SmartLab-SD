<?php

namespace App\Livewire\Admin;

use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Booking;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

#[Layout('layouts.admin')] 
class LabManager extends Component
{
    public Cluster $cluster;
    
    // Mode: 'manage' (atur gembok) atau 'booking' (lihat/pesan kursi)
    public $mode = 'booking'; 

    // Modal Booking (Create)
    public $showBookingModal = false;
    
    // Modal Info/Cancel (View/Delete) -- TAMBAHAN BARU
    public $showCancelModal = false; 

    // Data Seleksi
    public $selectedInventory = null;
    public $selectedBooking = null; // Menyimpan data booking aktif
    
    // Form Inputs
    public $userId, $startTime, $endTime, $notes;

    // Modal Cluster Booking
    public $showClusterModal = false;
    public $clusterId; // (Atau $selectedClusterId, sesuaikan kode Anda)

    // === TAMBAHKAN INI ===
    public $filterDate;
    public $filterTime;

    public function mount($clusterId)
    {
        // PERBAIKAN: Load Inventory BESERTA Booking yang aktif
        // Ini supaya di Denah Admin, kursi yang dibooking warnanya BIRU
        $this->cluster = Cluster::with(['inventory' => function($q) {
            $q->with(['bookings' => function($b) {
                $b->whereIn('status', ['booked', 'pending', 'approved'])
                  ->where('end_time', '>', now());
            }, 'bookings.user']); // Load user biar namanya muncul di tooltip/modal
        }])->findOrFail($clusterId);

        $this->clusterId = $clusterId;

        // === INISIALISASI DEFAULT ===
        // Agar tidak error saat pertama kali load
        $this->filterDate = now()->format('Y-m-d'); // Hari ini
        $this->filterTime = now()->format('H:i');
    }

    // --- FITUR 1: TOGGLE IS_BOOKABLE (VISUAL) ---
    public function toggleBookable($inventoryId)
    {
        // Hanya jalan jika mode 'manage' atau dipanggil dari tombol gembok kecil
        $item = Inventory::find($inventoryId);
        if($item) {
            $item->update(['is_bookable' => !$item->is_bookable]);
            $this->cluster->refresh(); // Refresh data agar warna gembok berubah
        }
    }

    // --- FITUR 2: INDIVIDUAL BOOKING / INFO ---
    // NAMA FUNGSI DIUBAH JADI 'selectSeat' AGAR COCOK DENGAN lab-seat.blade.php
    public function clickSeat($inventoryId)
    {
        // Jika mode manage, kita stop (biar toggleBookable yang bekerja lewat icon gembok)
        // Tapi jika tombol utama diklik, kita anggap ingin lihat detail
        if ($this->mode === 'manage'){
            $this->toggleBookable($inventoryId);
            return;
        }

        $this->selectedInventory = Inventory::find($inventoryId);
        if (!$this->selectedInventory) return;
        
        // Reset Variables
        $this->selectedBooking = null;
        $this->userId = Auth::id(); // Default form
        
        // 1. CARI BOOKING AKTIF DI KURSI INI
        $activeBooking = Booking::with('user')
            ->where('inventory_id', $inventoryId)
            ->whereIn('status', ['booked', 'active']) 
            ->where('end_time', '>', now())
            ->orderBy('id', 'desc')
            ->first();

        // 2. LOGIKA KEPUTUSAN
        if ($activeBooking) {
            // === KASUS A: SUDAH ADA YANG BOOKING ===
            // Tampilkan Modal Info & Cancel
            $this->selectedBooking = $activeBooking;
            
            $this->showCancelModal = true; 
            $this->showBookingModal = false;

        } else {
            // === KASUS B: KOSONG ===
            // Tampilkan Modal Buat Booking Baru
            
            // Cek apakah meja dikunci admin?
            if (!$this->selectedInventory->is_bookable) {
                // Opsional: Tetap izinkan admin booking walau dikunci, 
                // atau return error. Di sini kita izinkan Admin override.
                // session()->flash('error', 'Meja ini sedang ditutup.');
            }

            $this->resetBookingForm();
            $this->showBookingModal = true;
            $this->showCancelModal = false;
        }
    }

    public function getRoomBookingProperty()
    {
        // Gabungkan tanggal & jam dari input header
        try {
            $targetDateTime = Carbon::parse($this->filterDate . ' ' . $this->filterTime);
        } catch (\Exception $e) {
            return null; // Handle jika input tanggal tidak valid
        }

        // QUERY PENTING:
        // Cari booking di ruangan ini, PADA JAM YANG DIPILIH DI HEADER
        $activeBookings = Booking::query()
            ->whereHas('inventory', function ($q) {
                $q->where('cluster_id', $this->clusterId);
            })
            ->whereIn('status', ['active', 'booked']) // Sesuaikan status di DB Anda
            ->where(function ($query) use ($targetDateTime) {
                // Logika Tabrakan Waktu:
                // Booking Mulai SEBELUM target DAN Selesai SETELAH target
                $query->where('start_time', '<=', $targetDateTime)
                      ->where('end_time', '>', $targetDateTime);
            })
            ->with('user')
            ->get();

        // JIKA KOSONG -> RETURN NULL (Available)
        if ($activeBookings->isEmpty()) {
            return null;
        }

        // LOGIKA FULL BOOKING (Deteksi apakah 1 ruangan penuh)
        $totalUnits = Inventory::where('cluster_id', $this->clusterId)->count();
        
        // Buat object status buatan untuk dikirim ke Blade
        $status = new Booking();
        $status->is_mixed = true; // Default anggap campur dulu
        
        // Jika jumlah booking hampir sama dengan total unit (toleransi 1-2 PC rusak)
        // Dan peminjamnya mayoritas 1 orang
        if ($activeBookings->count() >= ($totalUnits - 2) && $activeBookings->unique('user_id')->count() === 1) {
            $status = $activeBookings->first(); // Ambil data asli salah satu booking
            $status->is_full_room = true; // Flag Full
        } else {
            // Data untuk status parsial
            $status->booked_count = $activeBookings->count();
            $status->total_count = $totalUnits;
            $status->users_list = $activeBookings->pluck('user.name')->unique()->take(3)->join(', ');
        }

        return $status;
    }

    
    // --- FITUR: SIMPAN BOOKING BARU (ADMIN CREATE) ---
    public function saveBooking()
    {
        $this->validate([
            'userId' => 'required',
            'startTime' => 'required|date',
            'endTime' => 'required|date|after:startTime',
        ]);

        Booking::create([
            'user_id' => $this->userId,
            'cluster_id' => $this->cluster->id,
            'inventory_id' => $this->selectedInventory->id,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'status' => 'booked', // Langsung approved/booked karena admin yang buat
            'notes' => $this->notes ?? 'Dibuat oleh Admin',
        ]);

        $this->showBookingModal = false;
        
        // Refresh cluster agar warna kursi langsung berubah biru
        $this->mount($this->cluster->id); 
        
        session()->flash('message', 'Booking berhasil dibuat.');
    }

    // --- FITUR: BATALKAN BOOKING (ADMIN CANCEL) ---
    public function cancelBooking()
    {
        if ($this->selectedBooking) {
            $this->selectedBooking->update(['status' => 'cancelled']);
            
            $this->showCancelModal = false;
            $this->selectedBooking = null;
            
            // Refresh tampilan
            $this->mount($this->cluster->id);

            session()->flash('message', 'Booking berhasil dibatalkan paksa.');
        }
    }

    // --- FITUR 3: BOOKING SATU RUANGAN (CLUSTER) ---
    public function saveClusterBooking()
    {
         $this->validate([
            'userId' => 'required',
            'startTime' => 'required|date',
            'endTime' => 'required|date|after:startTime',
        ]);

        $availableSeats = $this->cluster->inventory->where('is_bookable', true);

        foreach($availableSeats as $seat) {
            Booking::create([
                'user_id' => $this->userId,
                'cluster_id' => $this->cluster->id,
                'inventory_id' => $seat->id,
                'start_time' => $this->startTime,
                'end_time' => $this->endTime,
                'status' => 'booked',
                'notes' => 'Booking Ruangan: ' . $this->notes,
            ]);
        }

        $this->showClusterModal = false;
        $this->mount($this->cluster->id); // Refresh
        session()->flash('message', 'Satu ruangan berhasil dibooking.');
    }

    public function cancelClusterBooking($userId, $startTime)
    {
        $this->startTime = now()->format('Y-m-d\TH:i');
        $inventoryId = $this->cluster->inventory->pluck('id');
        // Cari data yang cocok dengan kriteria booking ruangan tadi
        $bookings = Booking::where('user_id', $userId)
            // ->where('cluster_id', $this->cluster->id) // Pastikan di ruangan yang benar
            ->whereIn('inventory_id', $inventoryId)
            ->where('start_time', $startTime)          // Pastikan jamnya sama
            ->where('status', ['booked', 'active']);               // Pastikan statusnya sesuai yang di-save

        // Hapus semua yang ditemukan
        // $bookings->delete();

        $bookings->update([
            'status' => 'cancelled',
            'updated_at' => now() 
        ]);

        // Refresh halaman
        $this->mount($this->cluster->id); 
        session()->flash('message', 'Booking ruangan berhasil dibatalkan.');
        $this->showClusterModal = false;
    }

    public function completeClusterBooking($userId, $startTime)
    {
        $this->startTime = now()->format('Y-m-d\TH:i');
        $inventoryId = $this->cluster->inventory->pluck('id');
        // Cari data yang cocok
        $bookings = Booking::where('user_id', $userId)
            // ->where('cluster_id', $this->cluster->id)
            ->whereIn('inventory_id', $inventoryId)
            ->where('start_time', $startTime)
            ->where('status', ['booked', 'active']);

        $this->showClusterModal = false;
        // Update semua yang ditemukan jadi completed
        // check_out_time diisi waktu sekarang (NOW)
        $bookings->update([
            'status' => 'completed',
            'updated_at' => now() 
        ]);
        
        // Refresh halaman
        $this->mount($this->cluster->id);
        session()->flash('message', 'Kelas selesai. Semua PC di ruangan ini telah dicompleted.');
    }

    public function resetBookingForm()
    {
        $this->userId = Auth::id(); 
        $this->startTime = now()->format('Y-m-d\TH:i');
        $this->endTime = now()->addHours(2)->format('Y-m-d\TH:i');
        $this->notes = '';
    }

    public function render()
    {
        return view('livewire.admin.lab-manager', [
            'users' => User::all(), // Untuk dropdown pilih user saat Admin membuat booking manual
        ]);
    }
}