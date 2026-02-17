<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Manager extends Component
{
    // ==========================================
    // 1. DATA UTAMA & FILTER
    // ==========================================
    public $clusterId;
    public $cluster;
    public $cancelReason = '';
    
    // Filter
    public $dateFilter;
    public $timeFilter;
    public $statusFilter = '';

    // ==========================================
    // 2. STATE MODAL: MANUAL BOOKING (SATUAN & RUANGAN)
    // ==========================================
    public $showCreateModal = false;
    
    // Mode Booking: 'unit' (PC Satuan) atau 'room' (Satu Ruangan Full)
    public $bookingMode = 'unit'; 

    // Data Holder
    public $selectedUnitId = null;
    public $selectedUnitName = '';
    
    // Form Inputs
    public $inputStartTime;
    public $inputEndTime;
    public $inputDescription;

    // User Search State
    public $userSearch = '';        
    public $selectedTargetUserId;   
    public $selectedTargetUserName; 
    public $usersList = [];         

    // ==========================================
    // 3. STATE MODAL: MANAGE (CANCEL / FINISH)
    // ==========================================
    public $showManageModal = false; // Gabungan Cancel/Finish biar rapi
    public $selectedBookingId;
    public $selectedBookingData;

    public $showClusterBookingModal = false;
    public $clusterStartTime;
    public $clusterEndTime;

    // ...
    public $showCancelModal = false; // <--- TAMBAHKAN INI
    public $showFinishModal = false; // Tambahkan juga ini jaga-jaga untuk modal selesai

    public $purpose;

    // ==========================================
    // LIFECYCLE
    // ==========================================
    public function mount($clusterId)
    {
        $this->clusterId = $clusterId;
        $this->cluster = Cluster::findOrFail($clusterId);
        $this->dateFilter = Carbon::today()->format('Y-m-d');
        $this->timeFilter = Carbon::now()->format('H:i');
    }

    // --- LOGIC PENCARIAN USER ---
    public function updatedUserSearch()
    {
        if (strlen($this->userSearch) > 1) {
            $this->usersList = User::where('name', 'like', '%' . $this->userSearch . '%')
                ->orWhere('email', 'like', '%' . $this->userSearch . '%')
                ->limit(5)
                ->get();
        } else {
            $this->usersList = [];
        }
    }

    public function selectUser($userId, $userName)
    {
        $this->selectedTargetUserId = $userId;
        $this->selectedTargetUserName = $userName;
        $this->userSearch = ''; 
        $this->usersList = [];  
    }

    public function resetSelectedUser()
    {
        $this->selectedTargetUserId = null;
        $this->selectedTargetUserName = null;
        $this->userSearch = '';
        $this->usersList = [];
    }

    // ==========================================
    // A. BUKA MODAL BOOKING
    // ==========================================
    
    // 1. Booking PC Satuan (Klik Kotak PC)
    public function openCreateUnitModal($unitId)
    {
        $unit = Inventory::find($unitId);
        if(!$unit) return;

        $this->bookingMode = 'unit';
        $this->selectedUnitId = $unit->id;
        $this->selectedUnitName = $unit->name;
        
        $this->prepareModalDefaults();
        $this->showCreateModal = true;
    }

    // 2. Booking Ruangan Full (Klik Tombol "Booking Ruangan") - NEW FEATURE
    public function openCreateRoomModal()
    {
        // Pastikan tipe cluster private (opsional, Admin bisa override kalau mau)
        // if ($this->cluster->type !== 'private') return;

        $this->bookingMode = 'room';
        $this->selectedUnitId = null; // KUNCI: Inventory Null
        $this->selectedUnitName = 'FULL ROOM: ' . $this->cluster->name;

        $this->prepareModalDefaults();
        $this->showCreateModal = true;
    }

    private function prepareModalDefaults()
    {
        // Smart Defaults Jam
        if ($this->timeFilter) {
            $start = Carbon::parse($this->dateFilter . ' ' . $this->timeFilter);
            $this->inputStartTime = $start->format('H:i');
            $this->inputEndTime = $start->addHours(2)->format('H:i');
        } else {
            $now = Carbon::now();
            // Bulatkan ke jam terdekat biar rapi
            $start = $now->copy()->addMinutes(15 - $now->minute % 15); 
            $this->inputStartTime = $start->format('H:i');
            $this->inputEndTime = $start->addHours(2)->format('H:i');
        }

        $this->inputDescription = '';
        $this->resetSelectedUser();
    }

    // ==========================================
    // B. PROSES SIMPAN (STORE)
    // ==========================================
    public function storeBooking()
    {
        // dd($this->all());
        $this->validate([
            'inputStartTime' => 'required',
            'inputEndTime'   => 'required|after:inputStartTime',
            'selectedTargetUserId' => 'required', 
            'purpose' => 'nullable|string|max:100',
        ], [
            'selectedTargetUserId.required' => 'Wajib pilih User/Peminjam.',
        ]);

        $startDateTime = $this->dateFilter . ' ' . $this->inputStartTime;
        $endDateTime   = $this->dateFilter . ' ' . $this->inputEndTime;

        // ----------------------------------------------------
        // LOGIC ANTI BENTROK (UNIFIED)
        // ----------------------------------------------------
        $conflict = Booking::where('cluster_id', $this->clusterId)
            ->where('status', '!=', 'cancelled')
            ->where(function($query) {
                if ($this->bookingMode === 'unit') {
                    // Jika Admin booking PC:
                    // Cek PC itu sendiri OR Cek Ruangan Full
                    $query->where('inventory_id', $this->selectedUnitId)
                          ->orWhereNull('inventory_id');
                } else {
                    // Jika Admin booking Ruangan:
                    // Cek SEMUA booking di cluster ini (PC satuan maupun Room)
                    // Tidak perlu filter inventory_id, semua inventory yg aktif bikin bentrok
                }
            })
            ->where(function($q) use ($startDateTime, $endDateTime) {
                // Logic Overlap Waktu
                $q->whereBetween('start_time', [$startDateTime, $endDateTime])
                  ->orWhereBetween('end_time', [$startDateTime, $endDateTime])
                  ->orWhere(function($sub) use ($startDateTime, $endDateTime) {
                      $sub->where('start_time', '<', $startDateTime)
                          ->where('end_time', '>', $endDateTime);
                  });
            })
            ->exists();

        if ($conflict) {
            session()->flash('error', 'GAGAL: Jadwal bentrok dengan booking lain (PC atau Ruangan sudah terisi).');
            return;
        }

        // ----------------------------------------------------
        // EKSEKUSI SIMPAN
        // ----------------------------------------------------
        Booking::create([
            'user_id'      => $this->selectedTargetUserId, 
            'cluster_id'   => $this->clusterId,       // Wajib
            'inventory_id' => $this->selectedUnitId,  // Bisa Null (Room) atau ID (Unit)
            'start_time'   => $startDateTime,
            'end_time'     => $endDateTime,
            'status'       => 'booked', // Admin langsung approve
            'purpose'      => $this->purpose,
            'description'  => $this->inputDescription ?? 'Admin Manual Booking',
            'type'         => 'admin_manual', // Opsional, penanda
        ]);

        $this->showCreateModal = false;
        $this->showClusterBookingModal = false;
        session()->flash('message', 'Booking berhasil dibuat!');
    }

    // ==========================================
    // C. MANAGE BOOKING (CANCEL/FINISH)
    // ==========================================
    public function openManageModal($bookingId)
    {
        $this->selectedBookingId = $bookingId;
        $this->selectedBookingData = Booking::with('user')->find($bookingId);
        $this->showManageModal = true;
    }

    public function openCancelModal($bookingId)
    {
        $this->selectedBookingId = $bookingId;
        $this->selectedBookingData = Booking::with('user')->find($bookingId);

        $this->showFinishModal = false;
        $this->showCancelModal = true;     
        $this->showCreateModal = false;
        $this->showClusterBookingModal = false;
        $this->cancelReason = '';
    }

    public function confirmCancel()
    {
        $this->validate([
            'cancelReason' => 'required|string|min:5',
        ], [
            'cancelReason.required' => 'Alasan pembatalan wajib diisi.',
            'cancelReason.min' => 'Alasan harus jelas (minimal 5 karakter).',
        ]);

        $booking = Booking::find($this->selectedBookingId);
        
        if ($booking) {
            $booking->update(['status' => 'cancelled', 'cancel_reason' => $this->cancelReason,]);
            session()->flash('message', 'Booking dibatalkan.');
        }

        // --- PERBAIKAN DISINI ---
        // Tutup modal yang BENAR (showCancelModal), bukan showManageModal
        $this->showCancelModal = false; 
        
        // (Opsional) Reset data agar bersih
        $this->reset(['selectedBookingId', 'selectedBookingData']);
    }

    public function openFinishModal($bookingId)
    {
        $this->selectedBookingId = $bookingId;
        $this->selectedBookingData = Booking::with('user')->find($bookingId);

        $this->showFinishModal = true;      // Buka Modal Finish
        $this->showCancelModal = false;     // Pastikan yang lain tutup
        $this->showCreateModal = false;
    }

    public function confirmFinish()
    {
        $booking = Booking::find($this->selectedBookingId);
        if ($booking) {
            $booking->update([
                'status' => 'completed',
                'end_time' => Carbon::now(),
            ]);
            session()->flash('message', 'Sesi diselesaikan.');
        }
        $this->showFinishModal = false; // Tutup Modal Finish yang benar
    }

    // [BARU] BUKA MODAL MASSAL
    public function openClusterBookingModal()
    {
        $this->resetSelectedUser(); // Reset pencarian user
        
        // Set Default Jam
        $this->clusterStartTime = '08:00';
        $this->clusterEndTime = '10:00';
        
        $this->showClusterBookingModal = true;
        $this->showCreateModal = false; // Tutup modal lain jika ada
    }

    // [BARU] LOGIC BOOKING SEMUA UNIT (LOOPING)
    public function bookEntireCluster()
    {
        // 1. Validasi
        $this->validate([
            'clusterStartTime' => 'required',
            'clusterEndTime'   => 'required|after:clusterStartTime',
            'selectedTargetUserId' => 'required',
        ], [
            'selectedTargetUserId.required' => 'Wajib memilih user/mahasiswa.',
            'clusterEndTime.after' => 'Jam selesai harus setelah jam mulai.'
        ]);

        // 2. Format Waktu
        $startDateTime = $this->dateFilter . ' ' . $this->clusterStartTime;
        $endDateTime   = $this->dateFilter . ' ' . $this->clusterEndTime;

        // 3. Ambil Unit yg Available (Fisik Bagus)
        $units = Inventory::where('cluster_id', $this->clusterId)
            ->where('status', 'available')
            ->get();

        if ($units->isEmpty()) {
            session()->flash('error', 'Tidak ada unit available di cluster ini.');
            return;
        }

        $successCount = 0;

        // 4. Looping & Booking Satu Per Satu
        foreach ($units as $unit) {
            
            // Cek Bentrok per Unit
            $isBooked = Booking::where('inventory_id', $unit->id)
                ->where('status', '!=', 'cancelled')
                ->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->whereBetween('start_time', [$startDateTime, $endDateTime])
                    ->orWhereBetween('end_time', [$startDateTime, $endDateTime])
                    ->orWhere(function($sub) use ($startDateTime, $endDateTime) {
                        $sub->where('start_time', '<', $startDateTime)
                            ->where('end_time', '>', $endDateTime);
                    });
                })
                ->exists();

            // Jika Kosong, Sikat!
            if (!$isBooked) {
                Booking::create([
                    'user_id'      => $this->selectedTargetUserId,
                    'cluster_id'   => $this->clusterId,
                    'inventory_id' => $unit->id,
                    'start_time'   => $startDateTime,
                    'end_time'     => $endDateTime,
                    'status'       => 'booked',
                    'description'  => 'Mass Booking (Event)',
                    'type'         => 'event_manual'
                ]);
                $successCount++;
            }
        }

        // 5. Tutup & Feedback
        $this->showClusterBookingModal = false;
        
        if ($successCount > 0) {
            session()->flash('message', "Berhasil membooking $successCount unit.");
        } else {
            session()->flash('error', "Gagal: Semua unit penuh di jam tersebut.");
        }
    }

    // ==========================================
    // D. RENDER VIEW
    // ==========================================
    #[Layout('layouts.admin')] 
    public function render()
    {
        // 1. Tentukan Waktu Pengecekan (Real-time atau Filter Manual)
        $checkTime = $this->timeFilter ? \Carbon\Carbon::parse($this->timeFilter) : now();

        // 2. Query Dasar
        $inventories = Inventory::where('cluster_id', $this->clusterId)
            ->when($this->statusFilter, function($query) use ($checkTime) {
                
            switch ($this->statusFilter) {
                    
                // KASUS 1: ADMIN MEMILIH "AVAILABLE"
                // Syarat: Fisik 'available' DAN Tidak ada bokingan di jam itu
                case 'available':
                    return $query->where('status', 'available')
                                ->whereDoesntHave('bookings', function($q) use ($checkTime) {
                                    $q->where('start_time', '<=', $checkTime)
                                    ->where('end_time', '>', $checkTime)
                                    ->whereIn('status', ['booked', 'active']);
                                });

                // KASUS 2: ADMIN MEMILIH "BOOKED"
                // Syarat: Fisik 'available' TAPI Sedang ada bokingan di jam itu
                case 'booked':
                    return $query->where('status', 'available')
                                ->whereHas('bookings', function($q) use ($checkTime) {
                                    $q->where('start_time', '<=', $checkTime)
                                    ->where('end_time', '>', $checkTime)
                                    ->whereIn('status', ['booked', 'active']);
                                });

                // KASUS 3: STATUS FISIK LAINNYA
                // Syarat: Cukup cek kolom status di tabel inventory
                case 'broken':
                    return $query->where('status', 'broken');
                    
                case 'maintenance':
                    return $query->where('status', 'maintenance');
            }
        })
        ->get();

        // 2. Cek apakah Ruangan SEDANG DIBOOKING FULL hari ini?
        // (Untuk ditampilkan di alert admin)
        $roomBookings = Booking::where('cluster_id', $this->clusterId)
            ->whereNull('inventory_id') // Ciri booking ruangan
            ->whereIn('status', ['booked', 'active'])
            ->whereDate('start_time', $this->dateFilter)
            ->with('user')
            ->get();

        // 3. Mapping Status PC (Time-Aware)
        foreach ($inventories as $pc) {
            $checkTime = $this->timeFilter 
                ? Carbon::parse($this->dateFilter . ' ' . $this->timeFilter)
                : Carbon::now(); // Jika tidak filter, pakai jam sekarang

            // Cari booking aktif untuk PC ini
            $pc->activeBooking = Booking::where('inventory_id', $pc->id)
                ->whereIn('status', ['booked', 'active'])
                ->where('start_time', '<=', $checkTime)
                ->where('end_time', '>', $checkTime)
                ->first();
                
            // Jika PC tidak ada booking, CEK APAKAH RUANGANNYA DIBOOKING?
            // (Agar admin lihat PC ini juga "merah" kalau ruangannya dibooking)
            if (!$pc->activeBooking) {
                $roomBookingNow = $roomBookings->first(function($rb) use ($checkTime) {
                    return $rb->start_time <= $checkTime && $rb->end_time > $checkTime;
                });
                
                if ($roomBookingNow) {
                    // Kita inject booking ruangan seolah-olah booking PC ini
                    // Agar di tampilan jadi merah/terisi
                    $pc->activeBooking = $roomBookingNow; 
                    $pc->isRoomBlocked = true; // Penanda visual tambahan
                }
            }
        }

        return view('livewire.admin.manage-lab', [
            'inventories' => $inventories,
            'roomBookings' => $roomBookings,
        ]);
    }
}