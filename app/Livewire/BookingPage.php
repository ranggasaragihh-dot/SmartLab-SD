<?php

namespace App\Livewire;

use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Booking;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Services\BookingService;

#[Layout('layouts.booking')]
class BookingPage extends Component
{
    public $selectedClusterId = null;
    public $cluster;

    public $filterDate;
    public $filterTime;
    
    public $start_time;
    public $end_time;
    
    public $selectedInventoryId = null; 
    public $selectedInventoryName = '';
    
    public $showBookModal = false;
    public $showCancelModal = false;
    public $showFinishModal = false;
    
    public $selectedBookingId = null;
    public $selectedBooking;

    public function mount($clusterId = null)
    {
        $this->filterDate = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d\TH:i');
        $this->filterTime = Carbon::now()->format('H:i');

        if ($clusterId) {
            $this->cluster = Cluster::findOrFail($clusterId);
        } else {
            $this->cluster = Cluster::where('is_active', true)->first();
        }

        if ($this->cluster) {
            $this->selectedClusterId = $this->cluster->id;
        }
    }

    public function updatedSelectedClusterId($value)
    {
        $this->cluster = Cluster::find($value);
        $this->reset(['selectedInventoryId', 'selectedInventoryName', 'start_time', 'end_time']);
        $this->resetModals();
    }

    private function resetModals()
    {
        $this->showBookModal = false;
        $this->showCancelModal = false;
        $this->showFinishModal = false;
        
        $this->selectedBookingId = null;
        $this->selectedBooking = null;
        
        $this->resetErrorBag();
    }

    public function clickSeat($inventoryId)
    {
        $this->resetModals();

        if (!$this->cluster && $this->selectedClusterId) {
            $this->cluster = Cluster::find($this->selectedClusterId);
        }

        $inventory = Inventory::find($inventoryId);
        
        if (!$inventory || in_array($inventory->status, ['broken', 'maintenance'])) {
            return; 
        }

        $this->selectedInventoryId = $inventory->id;
        $this->selectedInventoryName = $inventory->name;

        $myActiveBooking = Booking::where('inventory_id', $inventoryId)
            ->where('user_id', Auth::id())
            ->whereNotIn('status', ['cancelled', 'rejected', 'completed'])
            ->where('end_time', '>', Carbon::now())
            ->orderBy('start_time', 'asc')
            ->first();

        if ($myActiveBooking) {
            $this->handleMyBookingInteraction($myActiveBooking);
            return;
        }

        $user = Auth::user();
        
        $isLecturer = ($user->role === 'lecture'); 
        $isStudentTA = ((int)$user->ta === 1); 

        if ($this->cluster->type === 'private' && !($isLecturer || $isStudentTA)) {
            session()->flash('error', 'Cluster ini khusus Mahasiswa TA.');
            return;
        }

        if ($inventory->type === 'private' && !$isStudentTA && $user->role !== 'admin') {
            session()->flash('error', 'Unit ini khusus Mahasiswa TA.');
            return;
        }

        $baseDate = $this->filterDate ? Carbon::parse($this->filterDate) : Carbon::today();
        
        if ($baseDate->isToday()) {
            $defaultStart = Carbon::now()->addMinutes(5)->format('Y-m-d\TH:i');
        } else {
            $defaultStart = $baseDate->setTime(8, 0)->format('Y-m-d\TH:i');
        }
        
        $this->start_time = $defaultStart;
        $this->end_time = Carbon::parse($defaultStart)->addHours(1)->format('Y-m-d\TH:i');

        $this->showBookModal = true;
    }

    private function handleMyBookingInteraction($booking)
    {
        $this->selectedBookingId = $booking->id;
        $this->selectedBooking = $booking;
        
        $now = Carbon::now();
        $startTime = Carbon::parse($booking->start_time);

        if ($booking->status === 'active' || $now->gte($startTime)) {
            $this->showFinishModal = true;
        } else {
            $this->showCancelModal = true;
        }
    }

    public function createBooking()
    {
        $this->validate([
            'start_time' => 'required|date|after:now',
            'end_time'   => 'required|date|after:start_time',
        ]);

        $start = Carbon::parse($this->start_time);
        $end   = Carbon::parse($this->end_time);
        $diffMinutes = $start->diffInMinutes($end);

        $minAllowed = $this->cluster->min_duration_minutes ?? 0;
        if ($minAllowed > 0 && $diffMinutes < $minAllowed) {
            $this->addError('end_time', "Minimal durasi booking: {$minAllowed} menit.");
            return;
        }

        $maxAllowed = $this->cluster->max_duration_minutes;
        if ($maxAllowed && $diffMinutes > $maxAllowed) {
            $this->addError('end_time', "Maksimal durasi booking: " . ($maxAllowed/60) . " jam.");
            return;
        }

        $bookingService = app(BookingService::class);
        try {
            $bookingService->createBooking(
                Auth::id(),
                $this->selectedInventoryId,
                $this->cluster->id,
                $this->start_time,
                $this->end_time,
            );

            session()->flash('success', 'Booking berhasil!');
            $this->showBookModal = false;
            $this->reset(['start_time', 'end_time']);

        } catch (\Exception $e) {
            if (!$bookingService->isSlotAvailable(
                $this->selectedInventoryId,
                $this->cluster->id,
                $start,
                $end,
            )) {
                $this->addError('start_time', 'Slot baru saja diambil orang lain atau tidak tersedia.');
                return;
            }

            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function cancelBooking()
    {
        $booking = Booking::find($this->selectedBookingId);
        if (!$booking || $booking->user_id !== Auth::id()) return;

        $now = Carbon::now();
        $startTime = Carbon::parse($booking->start_time);

        if ($booking->status === 'active' || $now->gte($startTime)) {
            session()->flash('error', 'Sesi sudah berjalan, tidak bisa cancel.');
            $this->showCancelModal = false;
            return;
        }

        $booking->update(['status' => 'cancelled']);
        $this->resetModals();
        session()->flash('success', 'Booking dibatalkan.');
    }

    public function finishBooking()
    {
        $booking = Booking::find($this->selectedBookingId);
        if (!$booking || $booking->user_id !== Auth::id()) return;

        $booking->update([
            'status' => 'completed', 
            'end_time' => Carbon::now()
        ]); 
        
        $this->resetModals();
        session()->flash('success', 'Sesi selesai.');
    }

    public function processCheckIn()
    {
        $booking = Booking::find($this->selectedBookingId);
        if ($booking && $booking->user_id === Auth::id()) {
            $booking->update([
                'status' => 'active',
                'check_in_time' => Carbon::now()
            ]);
            session()->flash('success', 'Check-in Berhasil!');
            $this->resetModals(); 
        }
    }

    public function render()
    {
        if (!$this->cluster) {
            return view('livewire.booking-page', [
                'allowedClusters'     => collect(),
                'inventories'         => collect(),
                'bookingsMap'         => [],
                'isRoomLockedByAdmin' => false,
                'lockedInventories'   => [],
            ]);
        }

        $user = Auth::user();
        $allowedClusters = Cluster::where('is_active', true)
            ->when($user->role === 'student', function($q) {
                $q->where('type', 'public');
            })
            ->orderBy('name')
            ->get();

        $inventories = Inventory::with('item')
            ->where('cluster_id', $this->cluster->id)
            ->where('status', '!=', 'broken')
            ->orderBy('name', 'asc')
            ->get();

        $filterStart = Carbon::parse($this->filterDate)->startOfDay();
        $filterEnd   = Carbon::parse($this->filterDate)->endOfDay();

        $roomEvent = Booking::query()
            ->whereNull('inventory_id')
            ->whereIn('status', ['booked', 'active'])
            ->where(function($q) {
                $q->where('cluster_id', $this->cluster->id)
                  ->orWhereHas('cluster', function($subQuery) {
                      $subQuery->where('type', 'public');
                  });
            })
            ->where(function($q) use ($filterStart, $filterEnd) {
                $q->where('start_time', '<', $filterEnd)
                  ->where('end_time', '>', $filterStart);
            })
            ->first();

        $todayBookings = Booking::where('cluster_id', $this->cluster->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('inventory_id')
            ->where(function($q) use ($filterStart, $filterEnd) {
                 $q->where('start_time', '<', $filterEnd)
                   ->where('end_time', '>', $filterStart);
            })
            ->get();
        
        $bookingsMap = [];
        foreach($todayBookings as $b) {
            $bookingsMap[$b->inventory_id] = [
                'user_id' => $b->user_id,
                'status' => $b->status
            ];
        }

        $isRoomLockedByAdmin = false;
        $lockReason = '';
        $lockedInventories = [];
        $isAdmin = $user->roles->where('role', 'admin')->isNotEmpty();
        $isDosenOrTA = ((int)$user->ta === 1 || $user->roles->contains('role', 'lecturer'));

        if ($this->cluster->type === 'private') {
            if (!$isAdmin && !$isDosenOrTA) {
                $isRoomLockedByAdmin = true;
                $lockReason = "Akses Terbatas: Khusus Mahasiswa Tugas Akhir (TA)";
            } 
            elseif ($roomEvent && $roomEvent->cluster_id === $this->cluster->id && !$isAdmin) {
                $isRoomLockedByAdmin = true;
                $lockReason = $roomEvent->purpose ?? 'Lab TA sedang digunakan untuk Kegiatan Khusus';
            }
        }
        elseif ($this->cluster->type === 'public') {
            if ($roomEvent && !$isAdmin) {
                $isRoomLockedByAdmin = true;
                $lockReason = $roomEvent->purpose ?? 'Seluruh Lab Public ditutup untuk Event/Kegiatan';
            }
        }

        if ($isRoomLockedByAdmin) {
            $lockedInventories = $inventories->pluck('id')->toArray();
        }

        return view('livewire.booking-page', [
            'allowedClusters'     => $allowedClusters,
            'inventories'         => $inventories,
            'bookingsMap'         => $bookingsMap,
            'isRoomLockedByAdmin' => $isRoomLockedByAdmin,
            'lockedInventories'   => $lockedInventories,
            'lockReason'          => $lockReason,
            'currentClusterName'  => $this->cluster->name,
            'currentUserId'       => $user->id,
            'filterDate'          => $this->filterDate,
        ]);
    }
}