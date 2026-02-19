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
    public $sessionFilter = '';

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

    protected $sessions = [
        '1' => ['start' => '08:00', 'end' => '09.30'],
        '2' => ['start' => '10:00', 'end' => '12:30'],
        '3' => ['start' => '13:00', 'end' => '15:30'],
    ];

    // ==========================================
    // D. RENDER VIEW
    // ==========================================
    #[Layout('layouts.admin')] 
    public function render()
    {
        // 1. Tentukan Waktu Pengecekan secara Sentral (SATU PINTU)
        // Utamakan Sesi, lalu Time Filter, terakhir Now.
        if ($this->sessionFilter && isset($this->sessions[$this->sessionFilter])) {
            $session = $this->sessions[$this->sessionFilter];
            $sessionStart = \Carbon\Carbon::parse($this->dateFilter . ' ' . $session['start']);
            $sessionEnd = \Carbon\Carbon::parse($this->dateFilter . ' ' . $session['end']);
        } else {
            // Fallback jika tidak ada filter sesi (cek jam sekarang)
            $sessionStart = $this->timeFilter 
                ? \Carbon\Carbon::parse($this->dateFilter . ' ' . $this->timeFilter) 
                : now();
            $sessionEnd = $sessionStart->copy()->addMinute(); // Titik waktu saja
        }

        // 2. Query Inventories (Filter Utama)
        $inventories = Inventory::where('cluster_id', $this->clusterId)
        ->when($this->statusFilter, function($query) use ($sessionStart, $sessionEnd) {
            // Logika Overlap: 
            // Booking Start < Sesi End AND Booking End > Sesi Start
            $overlapQuery = function($q) use ($sessionStart, $sessionEnd) {
                $q->where('start_time', '<', $sessionEnd)
                  ->where('end_time', '>', $sessionStart)
                  ->whereIn('status', ['booked', 'active']);
            };
            switch ($this->statusFilter) {
                case 'available':
                    return $query->where('status', 'available')->whereDoesntHave('bookings', $overlapQuery);
                case 'booked':
                    return $query->where('status', 'available')->whereHas('bookings', $overlapQuery);
                case 'broken':
                    return $query->where('status', 'broken');
                case 'maintenance':
                    return $query->where('status', 'maintenance');
            }
        })
        ->get();

        // 3. Ambil Booking Ruangan (Global untuk cluster ini di waktu tersebut)
        $roomBookings = Booking::where('cluster_id', $this->clusterId)
            ->whereNull('inventory_id')
            ->whereIn('status', ['booked', 'active'])
            ->whereDate('start_time', $this->dateFilter) // Ambil semua booking ruangan di hari itu
            ->with('user')
            ->get();

        // Cek apakah di detik $checkTime ini ruangan sedang terpakai secara global
        // Tentukan apakah dalam RENTANG SESI ini ada booking ruangan
        $roomBookingNow = $roomBookings->first(function($rb) use ($sessionStart, $sessionEnd) {
            // Logika Irisan: Booking dimulai sebelum sesi berakhir 
            // DAN booking berakhir setelah sesi dimulai
            return $rb->start_time < $sessionEnd && $rb->end_time > $sessionStart;
        });

        // 4. Mapping Status PC (Gunakan $checkTime yang sudah konsisten!)
        foreach ($inventories as $pc) {
        $pc->activeBooking = Booking::where('inventory_id', $pc->id)
            ->whereIn('status', ['booked', 'active'])
            ->where('start_time', '<', $sessionEnd) // Booking dimulai sebelum sesi berakhir
            ->where('end_time', '>', $sessionStart)  // Booking berakhir setelah sesi dimulai
            ->first();
                
            // Jika PC bersih, tapi ruangan sedang diblokir, inject data ruangan
            if (!$pc->activeBooking && $roomBookingNow) {
                $pc->activeBooking = $roomBookingNow; 
                $pc->isRoomBlocked = true;
            }
        }

        return view('livewire.admin.manage-lab', [
            'inventories' => $inventories,
            'roomBookings' => $roomBookings,
        ]);
    }
}