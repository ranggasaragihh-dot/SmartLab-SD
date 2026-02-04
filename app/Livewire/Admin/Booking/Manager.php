<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\User; // Pastikan Model User di-import
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
    
    // Filter
    public $dateFilter;
    public $timeFilter; // [BARU] Filter Jam untuk Smart Default & View Context
    public $statusFilter = '';

    // ==========================================
    // 2. STATE MODAL: BOOKING CLUSTER (EVENT)
    // ==========================================
    public $showClusterBookingModal = false;
    public $clusterStartTime = '08:00';
    public $clusterEndTime = '10:00';
    public $clusterEventName;

    // ==========================================
    // 3. STATE MODAL: MANUAL / CANCEL / FINISH
    // ==========================================
    public $showCreateModal = false;
    public $showCancelModal = false;
    public $showFinishModal = false;

    // Data Holder
    public $selectedUnitId;
    public $selectedUnitName;
    public $selectedBookingId;
    public $selectedBookingData; 

    // Form Inputs (Manual Booking)
    public $inputStartTime;
    public $inputEndTime;
    public $inputDescription;

    // User Search State
    public $userSearch = '';        
    public $selectedTargetUserId;   
    public $selectedTargetUserName; 
    public $usersList = [];         

    // ==========================================
    // LIFECYCLE
    // ==========================================
    public function mount($clusterId)
    {
        $this->clusterId = $clusterId;
        $this->cluster = Cluster::findOrFail($clusterId);
        $this->dateFilter = Carbon::today()->format('Y-m-d');
        // $this->timeFilter default null
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
    // LOGIC B: CREATE MANUAL (SATUAN)
    // ==========================================
    public function openCreateModal($unitId)
    {
        $unit = Inventory::find($unitId);
        if(!$unit) return;

        $this->selectedUnitId = $unit->id;
        $this->selectedUnitName = $unit->name;
        
        // [SMART DEFAULTS]
        // Jika Admin memfilter jam tertentu, gunakan sebagai default input
        if ($this->timeFilter) {
            $start = Carbon::parse($this->dateFilter . ' ' . $this->timeFilter);
            $this->inputStartTime = $start->format('H:i');
            $this->inputEndTime = $start->addHours(2)->format('H:i');
        } else {
            // Jika tidak, gunakan jam sekarang
            $now = Carbon::now();
            $this->inputStartTime = $now->format('H:i');
            $this->inputEndTime = $now->addHours(2)->format('H:i');
        }

        $this->inputDescription = '';
        $this->resetSelectedUser(); // Reset state user search
        
        $this->showCreateModal = true;
    }

    public function bookEntireCluster()
    {
        // dd('Masuk Fungsi', $this->clusterStartTime, $this->clusterEndTime, $this->selectedTargetUserId);
        // 1. VALIDASI INPUT (Sesuai properti Admin)
        $this->validate([
            'clusterStartTime' => 'required',
            'clusterEndTime'   => 'required|after:clusterStartTime',
            // 'clusterEventName' => 'required|string|max:100',
            'selectedTargetUserId' => 'required',
        ]);

        // 2. AMBIL UNIT YANG "AVAILABLE" (Fisiknya Bagus)
        // Kita tidak mengambil 'is_bookable' dari model, tapi query manual status 'available'
        $availableUnits = Inventory::where('cluster_id', $this->clusterId)
            ->where('status', 'available') 
            ->get();

        if ($availableUnits->isEmpty()) {
            session()->flash('error', 'Tidak ada unit tersedia di cluster ini.');
            return;
        }

        // 3. SIAPKAN FORMAT WAKTU (Gabung Tanggal Filter + Jam Input)
        $startDateTime = $this->dateFilter . ' ' . $this->clusterStartTime;
        $endDateTime   = $this->dateFilter . ' ' . $this->clusterEndTime;

        $bookedCount = 0;

        // 4. LOOPING & CEK BENTROK (CRITICAL PART)
        foreach ($availableUnits as $unit) {
            
            // Cek apakah unit ini SUDAH ADA yang booking di jam tersebut?
            // (Logika Overlap: StartA < EndB  &&  EndA > StartB)
            $isBooked = Booking::where('inventory_id', $unit->id)
                ->where('status', '!=', 'cancelled') // Abaikan yang cancel
                ->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '<', $endDateTime)
                      ->where('end_time', '>', $startDateTime);
                })
                ->exists();

            // HANYA BOOKING JIKA KOSONG
            if (!$isBooked) {
                Booking::create([
                    'user_id'      => $this->selectedTargetUserId, // Admin sebagai pembuat
                    'cluster_id'   => $this->clusterId, // Opsional jika tabel booking punya kolom ini
                    'inventory_id' => $unit->id,
                    'start_time'   => $startDateTime,
                    'end_time'     => $endDateTime,
                    'status'       => 'booked',
                    'description'  => $this->clusterEventName . ' (Cluster Event)', // Notes -> Description
                    'type'         => 'event', // Tandai sebagai event
                ]);
                $bookedCount++;
            }
        }

        // 5. RESET & FEEDBACK
        $this->showClusterBookingModal = false;
        $this->reset(['clusterEventName']);
        $this->resetSelectedUser();
        
        if ($bookedCount > 0) {
            session()->flash('message', "Berhasil membooking $bookedCount unit untuk event.");
        } else {
            session()->flash('error', "Gagal: Semua unit penuh di jam tersebut.");
        }
    }

    public function storeBooking()
    {
        $this->validate([
            'inputStartTime' => 'required',
            'inputEndTime'   => 'required|after:inputStartTime',
            'selectedTargetUserId' => 'required', 
            'inputDescription' => 'nullable|string|max:100',
        ], [
            'selectedTargetUserId.required' => 'Anda wajib memilih user/mahasiswa.',
        ]);

        // Format DateTime Lengkap
        $inputStart = $this->dateFilter . ' ' . $this->inputStartTime;
        $inputEnd   = $this->dateFilter . ' ' . $this->inputEndTime;

        // [CEK BENTROK - OVERLAP LOGIC]
        // Memastikan jam 14:00 aman dipesan walau ada yang selesai jam 14:00
        $isBooked = Booking::where('inventory_id', $this->selectedUnitId)
            ->whereDate('start_time', $this->dateFilter)
            ->where('status', '!=', 'cancelled')
            ->where(function($q) use ($inputStart, $inputEnd) {
                // Rumus: (ExistingStart < NewEnd) AND (ExistingEnd > NewStart)
                $q->where('start_time', '<', $inputEnd)
                  ->where('end_time', '>', $inputStart);
            })
            ->exists();

        if ($isBooked) {
            session()->flash('error', 'Gagal: Jam tersebut bentrok dengan jadwal lain.');
            return;
        }

        Booking::create([
            'user_id'      => $this->selectedTargetUserId, 
            'inventory_id' => $this->selectedUnitId,
            'start_time'   => $inputStart,
            'end_time'     => $inputEnd,
            'status'       => 'booked',
            'description'  => $this->inputDescription ?? 'Manual Booking',
            'type'         => 'admin_manual',
        ]);

        $this->showCreateModal = false;
        session()->flash('message', 'Booking manual untuk ' . $this->selectedTargetUserName . ' berhasil.');
    }

    // ==========================================
    // LOGIC C & D: CANCEL & FINISH MODAL
    // ==========================================
    public function openCancelModal($bookingId)
    {
        $this->selectedBookingId = $bookingId;
        $this->selectedBookingData = Booking::with('user')->find($bookingId);
        $this->showCancelModal = true;
    }

    public function confirmCancel()
    {
        $booking = Booking::find($this->selectedBookingId);
        if ($booking) {
            $booking->update(['status' => 'cancelled']);
            session()->flash('message', 'Booking berhasil dibatalkan.');
        }
        $this->showCancelModal = false;
    }

    public function openFinishModal($bookingId)
    {
        $this->selectedBookingId = $bookingId;
        $this->selectedBookingData = Booking::with('user')->find($bookingId);
        $this->showFinishModal = true;
    }

    public function confirmFinish()
    {
        $booking = Booking::find($this->selectedBookingId);
        if ($booking) {
            $booking->update([
                'status' => 'completed',
                'end_time' => Carbon::now(),
            ]);
            session()->flash('message', 'Sesi diselesaikan paksa.');
        }
        $this->showFinishModal = false;
    }

    // ==========================================
    // RENDER
    // ==========================================
    #[Layout('layouts.admin')] 
    public function render()
    {
        $query = Inventory::where('cluster_id', $this->clusterId);

        // Filter Status Fisik
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Ambil Data Inventories
        $inventories = $query->get();

        // [LOGIC PENENTUAN STATUS (TIME-AWARE)]
        foreach ($inventories as $pc) {
            if ($this->timeFilter) {
                // Jika Filter Jam Aktif: Cari booking yang overlap dengan jam filter
                $filterDateTime = $this->dateFilter . ' ' . $this->timeFilter;
                
                $pc->activeBooking = $pc->bookings()
                    ->whereDate('start_time', $this->dateFilter)
                    ->where('status', 'booked')
                    ->where('start_time', '<=', $filterDateTime) // Mulai sebelum/pas jam filter
                    ->where('end_time', '>', $filterDateTime)   // Selesai SETELAH jam filter
                    ->first();
            } else {
                // Jika Tidak Filter Jam: Default (Realtime / First of Day)
                $now = Carbon::now();
                
                if($this->dateFilter != $now->format('Y-m-d')) {
                     // Jika lihat tanggal lain, ambil booking pertama
                     $pc->activeBooking = $pc->bookings()
                        ->whereDate('start_time', $this->dateFilter)
                        ->where('status', 'booked')
                        ->orderBy('start_time')
                        ->first();
                } else {
                    // Jika hari ini, cari yang sedang berjalan saat ini
                    $pc->activeBooking = $pc->bookings()
                        ->where('status', 'booked')
                        ->where('start_time', '<=', $now)
                        ->where('end_time', '>', $now)
                        ->first();
                }
            }
        }

        return view('livewire.admin.manage-lab', [
            'inventories' => $inventories
        ]);
    }
}