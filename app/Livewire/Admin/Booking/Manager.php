<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Support\Facades\DB;
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
    public $eventConflicts = [];


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
            $this->usersList = User::where('is_banned', false)
                ->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->userSearch . '%')
                        ->orWhere('nomor_induk', 'like', '%' . $this->userSearch . '%')
                        ->orWhere('email', 'like', '%' . $this->userSearch . '%');
                })
                ->limit(5)
                ->get();
        } else {
            $this->usersList = [];
        }
    }

    public function selectUser($userId)
    {
        // Nama diambil dari database: aman untuk nama yang mengandung tanda kutip.
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        $this->selectedTargetUserId = $user->id;
        $this->selectedTargetUserName = $user->name;
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

    /**
     * Buka modal "Book 1 Ruangan (Event)" (Buku Panduan 4.5).
     */
    public function openEventModal()
    {
        $this->bookingMode = 'room';
        $this->selectedUnitId = null;
        $this->selectedUnitName = 'FULL ROOM: ' . $this->cluster->name;
        $this->purpose = '';
        $this->eventConflicts = [];

        $this->prepareModalDefaults();
        $this->resetValidation();
        $this->showCreateModal = false;
        $this->showClusterBookingModal = true;
    }

    /**
     * Simpan event ruangan penuh (SKPL UC-04 / DPPL "On Mass Booking"):
     * 1. deteksi booking individu yang bentrok, 2. tampilkan peringatan,
     * 3. jika admin melanjutkan, booking itu dibatalkan lalu ruangan diblokir.
     */
    public function storeEventBooking(bool $confirmed = false)
    {
        $this->validate([
            'inputStartTime' => 'required|date_format:H:i',
            'inputEndTime'   => 'required|date_format:H:i|after:inputStartTime',
            'selectedTargetUserId' => 'required|exists:users,id',
            'purpose' => 'required|string|max:100',
        ], [
            'selectedTargetUserId.required' => 'Wajib pilih penanggung jawab.',
            'purpose.required' => 'Tujuan / agenda acara wajib diisi.',
            'inputEndTime.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $start = Carbon::parse($this->dateFilter . ' ' . $this->inputStartTime);
        $end = Carbon::parse($this->dateFilter . ' ' . $this->inputEndTime);

        if ($end->lte(now())) {
            $this->addError('inputEndTime', 'Waktu event sudah lewat.');
            return;
        }

        $overlappingEvent = Booking::where('cluster_id', $this->clusterId)
            ->whereNull('inventory_id')
            ->whereIn('status', ['booked', 'active'])
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();
        if ($overlappingEvent) {
            $this->addError('inputStartTime', 'Sudah ada event lain di ruangan ini pada jam tersebut.');
            return;
        }

        $conflicts = Booking::with(['user:id,name', 'inventory:id,name'])
            ->where('cluster_id', $this->clusterId)
            ->whereNotNull('inventory_id')
            ->whereIn('status', ['booked', 'active'])
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->orderBy('start_time')
            ->get();

        $conflictIds = $conflicts->pluck('id')->sort()->values()->all();
        $warnedIds = collect($this->eventConflicts)->pluck('id')->sort()->values()->all();

        // Peringatan dulu; lanjut hanya jika admin mengonfirmasi daftar konflik yang sama.
        if ($conflicts->isNotEmpty() && (!$confirmed || $conflictIds !== $warnedIds)) {
            $this->eventConflicts = $conflicts->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'user' => $booking->user?->name ?? '-',
                'unit' => $booking->inventory?->name ?? '-',
                'time' => $booking->start_time->format('H:i') . ' - ' . $booking->end_time->format('H:i'),
                'status' => $booking->status === 'active' ? 'Sedang dipakai' : 'Belum check-in',
            ])->all();
            return;
        }

        DB::transaction(function () use ($conflictIds, $start, $end) {
            if ($conflictIds) {
                Booking::whereIn('id', $conflictIds)->update([
                    'status' => 'cancelled',
                    'cancel_reason' => 'Dibatalkan admin: ruangan dipakai untuk event "' . $this->purpose . '"',
                ]);
            }

            Booking::create([
                'user_id'      => $this->selectedTargetUserId,
                'cluster_id'   => $this->clusterId,
                'inventory_id' => null,
                'start_time'   => $start,
                'end_time'     => $end,
                'status'       => 'booked',
                'purpose'      => $this->purpose,
            ]);
        });

        $cancelled = count($conflictIds);
        $this->eventConflicts = [];
        $this->showClusterBookingModal = false;
        session()->flash('message', 'Reservasi event berhasil dibuat.' . ($cancelled ? " {$cancelled} booking individu dibatalkan." : ''));
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

    /**
     * Booking manual satu PC oleh admin (tombol "+ Manual").
     */
    public function storeBooking()
    {
        $this->validate([
            'inputStartTime' => 'required|date_format:H:i',
            'inputEndTime'   => 'required|date_format:H:i|after:inputStartTime',
            'selectedTargetUserId' => 'required|exists:users,id',
            'selectedUnitId' => 'required',
            'purpose' => 'nullable|string|max:100',
        ], [
            'selectedTargetUserId.required' => 'Wajib pilih User/Peminjam.',
            'selectedUnitId.required' => 'Pilih unit PC terlebih dahulu.',
        ]);

        $cluster = Cluster::findOrFail($this->clusterId);
        $targetUser = User::findOrFail($this->selectedTargetUserId);
        if (!$cluster->canBeBookedBy($targetUser)) {
            session()->flash('error', 'Cluster ini hanya dapat dibooking untuk mahasiswa yang sudah mengambil TA.');
            return;
        }

        $startDateTime = $this->dateFilter . ' ' . $this->inputStartTime;
        $endDateTime   = $this->dateFilter . ' ' . $this->inputEndTime;
        $unitId = $this->selectedUnitId;

        if (Carbon::parse($endDateTime)->lte(now())) {
            $this->addError('inputEndTime', 'Waktu booking sudah lewat.');
            return;
        }

        if (!Inventory::where('cluster_id', $this->clusterId)->whereKey($unitId)->exists()) {
            session()->flash('error', 'Unit tidak berada di cluster ini.');
            return;
        }

        if (!app(BookingService::class)->isSlotAvailable($unitId, $this->clusterId, $startDateTime, $endDateTime)) {
            session()->flash('error', 'GAGAL: Jadwal bentrok dengan booking lain (PC atau Ruangan sudah terisi).');
            return;
        }

        Booking::create([
            'user_id'      => $this->selectedTargetUserId,
            'cluster_id'   => $this->clusterId,
            'inventory_id' => $unitId,
            'start_time'   => $startDateTime,
            'end_time'     => $endDateTime,
            'status'       => 'booked',
            'purpose'      => $this->purpose,
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

        if ($booking && in_array($booking->status, ['booked', 'active'])) {
            $booking->update(['status' => 'cancelled', 'cancel_reason' => $this->cancelReason,]);
            session()->flash('message', 'Booking dibatalkan.');
        } else {
            session()->flash('error', 'Booking ini sudah selesai atau dibatalkan.');
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
        if ($booking && $booking->status === 'active') {
            $booking->update([
                'status' => 'completed',
                'end_time' => Carbon::now(),
            ]);
            session()->flash('message', 'Sesi diselesaikan.');
        } else {
            session()->flash('error', 'Hanya sesi yang sudah check-in yang bisa diselesaikan. Gunakan Batalkan untuk booking yang belum check-in.');
        }
        $this->showFinishModal = false;
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