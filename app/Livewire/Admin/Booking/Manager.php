<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Manager extends Component
{
    public $clusterId;
    public $cluster;
    public $cancelReason = '';
    
    public $dateFilter;
    public $timeFilter;
    public $statusFilter = '';

    public $showCreateModal = false;
    
    // Mode Booking: 'unit' (PC Satuan) atau 'room' (Satu Ruangan Full)
    public $bookingMode = 'unit'; 

    public $selectedUnitId = null;
    public $selectedUnitName = '';
    
    public $inputStartTime;
    public $inputEndTime;
    public $inputDescription;

    public $userSearch = '';        
    public $selectedTargetUserId;   
    public $selectedTargetUserName; 
    public $usersList = [];         

    public $showManageModal = false;
    public $selectedBookingId;
    public $selectedBookingData;

    public $showClusterBookingModal = false;
    public $clusterStartTime;
    public $clusterEndTime;


    public $showCancelModal = false;
    public $showFinishModal = false;

    public $purpose;
    public $sessionFilter = '';

    public function mount($clusterId)
    {
        $this->clusterId = $clusterId;
        $this->cluster = Cluster::findOrFail($clusterId);
        $this->dateFilter = Carbon::today()->format('Y-m-d');
        $this->timeFilter = Carbon::now()->format('H:i');
    }

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

    public function openCreateRoomModal()
    {
        $this->bookingMode = 'room';
        $this->selectedUnitId = null;
        $this->selectedUnitName = 'FULL ROOM: ' . $this->cluster->name;

        $this->prepareModalDefaults();
        $this->showCreateModal = true;
    }

    private function prepareModalDefaults()
    {
        if ($this->timeFilter) {
            $start = Carbon::parse($this->dateFilter . ' ' . $this->timeFilter);
            $this->inputStartTime = $start->format('H:i');
            $this->inputEndTime = $start->addHours(2)->format('H:i');
        } else {
            $now = Carbon::now();
            $start = $now->copy()->addMinutes(15 - $now->minute % 15); 
            $this->inputStartTime = $start->format('H:i');
            $this->inputEndTime = $start->addHours(2)->format('H:i');
        }

        $this->inputDescription = '';
        $this->resetSelectedUser();
    }

    public function storeBooking()
    {
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

        $conflict = Booking::where('cluster_id', $this->clusterId)
            ->where('status', '!=', 'cancelled')
            ->where(function($query) {
                if ($this->bookingMode === 'unit') {
                    $query->where('inventory_id', $this->selectedUnitId)
                          ->orWhereNull('inventory_id');
                }
            })
            ->where(function($q) use ($startDateTime, $endDateTime) {
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

        Booking::create([
            'user_id'      => $this->selectedTargetUserId, 
            'cluster_id'   => $this->clusterId,
            'inventory_id' => $this->selectedUnitId,
            'start_time'   => $startDateTime,
            'end_time'     => $endDateTime,
            'status'       => 'booked',
            'purpose'      => $this->purpose,
            'description'  => $this->inputDescription ?? 'Admin Manual Booking',
        ]);

        $this->showCreateModal = false;
        $this->showClusterBookingModal = false;
        session()->flash('message', 'Booking berhasil dibuat!');
    }

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

        $this->showCancelModal = false; 
        $this->reset(['selectedBookingId', 'selectedBookingData']);
    }

    public function openFinishModal($bookingId)
    {
        $this->selectedBookingId = $bookingId;
        $this->selectedBookingData = Booking::with('user')->find($bookingId);

        $this->showFinishModal = true;
        $this->showCancelModal = false;
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
        $this->showFinishModal = false;
    }

    public function openClusterBookingModal()
    {
        $this->resetSelectedUser();
        $this->clusterStartTime = '08:00';
        $this->clusterEndTime = '10:00';
        $this->showClusterBookingModal = true;
        $this->showCreateModal = false;
    }

    public function bookEntireCluster()
    {
        $this->validate([
            'clusterStartTime' => 'required',
            'clusterEndTime'   => 'required|after:clusterStartTime',
            'selectedTargetUserId' => 'required',
        ], [
            'selectedTargetUserId.required' => 'Wajib memilih user/mahasiswa.',
            'clusterEndTime.after' => 'Jam selesai harus setelah jam mulai.'
        ]);

        $startDateTime = $this->dateFilter . ' ' . $this->clusterStartTime;
        $endDateTime   = $this->dateFilter . ' ' . $this->clusterEndTime;

        $units = Inventory::where('cluster_id', $this->clusterId)
            ->where('status', 'available')
            ->get();

        if ($units->isEmpty()) {
            session()->flash('error', 'Tidak ada unit available di cluster ini.');
            return;
        }

        $successCount = 0;

        foreach ($units as $unit) {
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

    #[Layout('layouts.admin')] 
    public function render()
    {
        if ($this->sessionFilter && isset($this->sessions[$this->sessionFilter])) {
            $session = $this->sessions[$this->sessionFilter];
            $sessionStart = \Carbon\Carbon::parse($this->dateFilter . ' ' . $session['start']);
            $sessionEnd = \Carbon\Carbon::parse($this->dateFilter . ' ' . $session['end']);
        } else {
            $sessionStart = $this->timeFilter 
                ? \Carbon\Carbon::parse($this->dateFilter . ' ' . $this->timeFilter) 
                : now();
            $sessionEnd = $sessionStart->copy()->addMinute();
        }

        $inventories = Inventory::where('cluster_id', $this->clusterId)
        ->when($this->statusFilter, function($query) use ($sessionStart, $sessionEnd) {
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

        $roomBookings = Booking::where('cluster_id', $this->clusterId)
            ->whereNull('inventory_id')
            ->whereIn('status', ['booked', 'active'])
            ->whereDate('start_time', $this->dateFilter)
            ->with('user')
            ->get();

        $roomBookingNow = $roomBookings->first(function($rb) use ($sessionStart, $sessionEnd) {
            return $rb->start_time < $sessionEnd && $rb->end_time > $sessionStart;
        });

        foreach ($inventories as $pc) {
        $pc->activeBooking = Booking::where('inventory_id', $pc->id)
            ->whereIn('status', ['booked', 'active'])
            ->where('start_time', '<', $sessionEnd)
            ->where('end_time', '>', $sessionStart)
            ->first();

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