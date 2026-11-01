<?php

namespace App\Livewire;

use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Booking;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
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

    public $start_hour;
    public $start_minute;
    public $end_hour;
    public $end_minute;

    public $start_time;
    public $end_time;
    
    // Locked: hanya boleh diisi server (clickSeat), tidak bisa diubah dari browser.
    #[Locked]
    public $selectedInventoryId = null;
    #[Locked]
    public $selectedInventoryName = '';

    public $showBookModal = false;
    public $showCancelModal = false;
    public $showFinishModal = false;

    #[Locked]
    public $selectedBookingId = null;
    public $selectedBooking;

    public function mount($clusterId = null)
    {
        $this->filterDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->filterTime = Carbon::now()->format('H:i');

        if ($clusterId) {
            $this->cluster = $this->allowedClustersQuery(Auth::user())->findOrFail($clusterId);
        } else {
            $this->cluster = $this->allowedClustersQuery(Auth::user())->orderBy('name')->first();
        }

        if ($this->cluster) {
            $this->selectedClusterId = $this->cluster->id;
        }
    }

    /**
     * Cluster yang boleh dibuka user ini (sama dengan daftar di sidebar).
     */
    private function allowedClustersQuery(User $user)
    {
        return Cluster::where('is_active', true)
            ->when($user->hasRole('student'), fn ($q) => $q->where('type', 'public'));
    }

    public function updatedSelectedClusterId($value)
    {
        $cluster = $this->allowedClustersQuery(Auth::user())->find($value);
        if (!$cluster) {
            $this->selectedClusterId = $this->cluster?->id;
            return;
        }

        $this->cluster = $cluster;
        $this->reset([
            'selectedInventoryId',
            'selectedInventoryName',
            'start_time', 'end_time',
            'start_hour', 'start_minute',
            'end_hour', 'end_minute',
            'filterDate'
        ]);
        $this->filterDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
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
        app(BookingService::class)->synchronizeExpiredBookings();

        if (!$this->cluster && $this->selectedClusterId) {
            $this->cluster = Cluster::find($this->selectedClusterId);
        }

        $inventory = Inventory::find($inventoryId);

        if (!$inventory || (int) $inventory->cluster_id !== (int) $this->cluster?->id || in_array($inventory->status, ['broken', 'maintenance']) || !$inventory->is_bookable) {
            return;
        }

        $this->selectedInventoryId = $inventory->id;
        $this->selectedInventoryName = $inventory->name;

        $selectedDateStart = Carbon::parse($this->filterDate, 'Asia/Jakarta')->startOfDay();
        $selectedDateEnd = $selectedDateStart->copy()->endOfDay();

        $myActiveBooking = Booking::where('inventory_id', $inventoryId)
            ->where('user_id', Auth::id())
            ->whereNotIn('status', ['cancelled', 'rejected', 'completed'])
            ->where('end_time', '>', Carbon::now())
            ->where('start_time', '<', $selectedDateEnd)
            ->where('end_time', '>', $selectedDateStart)
            ->orderBy('start_time', 'asc')
            ->first();

        if ($myActiveBooking) {
            $this->handleMyBookingInteraction($myActiveBooking);
            return;
        }

        $blockReason = $this->bookingBlockReason(Auth::user(), $inventory);
        if ($blockReason) {
            $this->selectedInventoryId = null;
            $this->selectedInventoryName = '';
            session()->flash('error', $blockReason);
            return;
        }

        $this->filterDate = $this->filterDate ? Carbon::parse($this->filterDate)->format('Y-m-d') : Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $this->start_hour = null;
        $this->start_minute = null;
        $this->end_hour = null;
        $this->end_minute = null;
        $this->start_time = null;
        $this->end_time = null;

        $this->showBookModal = true;
    }

    /**
     * Alasan user tidak boleh membooking unit ini, atau null jika boleh.
     * Dipakai saat kursi diklik dan diulang saat booking disimpan.
     */
    private function bookingBlockReason(User $user, ?Inventory $inventory): ?string
    {
        $cluster = $this->cluster;

        if (!$cluster || !$cluster->is_active) {
            return 'Cluster ini sedang tidak aktif.';
        }

        if (!$inventory || (int) $inventory->cluster_id !== (int) $cluster->id) {
            return 'Pilih unit PC pada cluster ini terlebih dahulu.';
        }

        if (in_array($inventory->status, ['broken', 'maintenance']) || !$inventory->is_bookable) {
            return 'Unit ini sedang tidak dapat digunakan.';
        }

        if (!$cluster->canBeBookedBy($user)) {
            return 'Cluster ini hanya dapat dibooking oleh mahasiswa yang sudah mengambil TA.';
        }

        if ($cluster->type === 'private' && !($user->hasRole('lecturer') || (bool) $user->ta)) {
            return 'Cluster ini khusus Mahasiswa TA.';
        }

        return null;
    }

    public function updatedFilterDate()
    {
        $this->occupiedCache = null;
        $this->reset(['start_hour', 'start_minute', 'end_hour', 'end_minute', 'start_time', 'end_time']);
    }

    /** Cache per request (tidak dikirim ke browser). */
    private ?array $occupiedCache = null;

    /**
     * Rentang waktu yang sudah terpakai untuk PC terpilih pada tanggal terpilih:
     * booking PC ini, event ruangan di cluster ini, dan booking user sendiri di PC lain.
     */
    private function occupiedIntervals(): array
    {
        if ($this->occupiedCache !== null) {
            return $this->occupiedCache;
        }

        if (!$this->selectedInventoryId || !$this->cluster) {
            return $this->occupiedCache = [];
        }

        $dayStart = Carbon::parse($this->filterDate ?: Carbon::now('Asia/Jakarta')->toDateString(), 'Asia/Jakarta')->startOfDay();

        return $this->occupiedCache = Booking::query()
            ->whereIn('status', ['booked', 'active'])
            ->where('start_time', '<', $dayStart->copy()->addDays(2))
            ->where('end_time', '>', $dayStart)
            ->where(function ($query) {
                $query->where('inventory_id', $this->selectedInventoryId)
                    ->orWhere(fn ($room) => $room->where('cluster_id', $this->cluster->id)->whereNull('inventory_id'))
                    ->orWhere('user_id', Auth::id());
            })
            ->get(['start_time', 'end_time'])
            ->map(fn ($booking) => [$booking->start_time, $booking->end_time])
            ->all();
    }

    private function overlapsOccupied(Carbon $start, Carbon $end): bool
    {
        foreach ($this->occupiedIntervals() as [$occupiedStart, $occupiedEnd]) {
            if ($occupiedStart->lt($end) && $occupiedEnd->gt($start)) {
                return true;
            }
        }

        return false;
    }

    public function updatedStartHour()
    {
        $this->reset(['start_minute', 'end_hour', 'end_minute', 'start_time', 'end_time']);
    }

    public function updatedStartMinute()
    {
        $this->reset(['end_hour', 'end_minute', 'start_time', 'end_time']);
    }

    public function updatedEndHour()
    {
        $this->reset(['end_minute', 'end_time']);
    }

    public function isStartHourDisabled($hour)
    {
        if ($hour === null || $hour === '') {
            return false;
        }

        foreach (range(0, 59) as $minute) {
            if (!$this->isTimeOptionDisabled('start', $hour, str_pad((string) $minute, 2, '0', STR_PAD_LEFT))) {
                return false;
            }
        }

        return true;
    }

    public function isTimeOptionDisabled($type, $hour, $minute)
    {
        if ($hour === null || $hour === '' || $minute === null || $minute === '') {
            return false;
        }

        $selectedDate = $this->filterDate ? Carbon::parse($this->filterDate)->format('Y-m-d') : Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $candidate = Carbon::parse($selectedDate, 'Asia/Jakarta')->setTime((int) $hour, (int) $minute, 0);

        if ($type === 'start') {
            if ($candidate->lt(Carbon::now('Asia/Jakarta'))) {
                return true;
            }

            // Mulai di jam ini hanya masuk akal jika durasi minimal muat sebelum booking berikutnya.
            $minimumEnd = $candidate->copy()->addMinutes((int) ($this->cluster->min_duration_minutes ?? 30));

            return $this->overlapsOccupied($candidate, $minimumEnd);
        }

        if ($type === 'end') {
            if ($this->start_hour === null || $this->start_minute === null) {
                return false;
            }

            $start = Carbon::parse($selectedDate, 'Asia/Jakarta')->setTime((int) $this->start_hour, (int) $this->start_minute, 0);
            if ($candidate->lte($start)) {
                $candidate->addDay();
            }

            if ($selectedDate === Carbon::now('Asia/Jakarta')->format('Y-m-d') && $candidate->lt(Carbon::now('Asia/Jakarta'))) {
                return true;
            }

            $minAllowed = $start->copy()->addMinutes((int) ($this->cluster->min_duration_minutes ?? 30));
            $maxAllowed = $start->copy()->addMinutes((int) ($this->cluster->max_duration_minutes ?? 120));

            return $candidate->lt($minAllowed) || $candidate->gt($maxAllowed) || $this->overlapsOccupied($start, $candidate);
        }

        return false;
    }

    public function isEndHourDisabled($hour)
    {
        if ($hour === null || $hour === '') {
            return false;
        }

        if ($this->start_hour === null || $this->start_minute === null) {
            return false;
        }

        foreach (range(0, 59) as $minute) {
            $formattedMinute = str_pad((string) $minute, 2, '0', STR_PAD_LEFT);
            if (!$this->isTimeOptionDisabled('end', $hour, $formattedMinute)) {
                return false;
            }
        }

        return true;
    }

    private function buildDateTimeFromInputs($date, $hour, $minute, $after = null)
    {
        if (!$date || $hour === null || $minute === null) {
            return null;
        }

        $dateTime = Carbon::parse($date, 'Asia/Jakarta')->setTime((int) $hour, (int) $minute, 0);

        if ($after && $dateTime->lte(Carbon::parse($after, 'Asia/Jakarta'))) {
            $dateTime->addDay();
        }

        return $dateTime->format('Y-m-d H:i:s');
    }

    private function handleMyBookingInteraction($booking)
    {
        $this->selectedBookingId = $booking->id;
        $this->selectedBooking = $booking;
        
        if ($booking->status === 'active' && $booking->check_in_time !== null) {
            $this->showFinishModal = true;
        } elseif ($booking->status === 'booked') {
            // Belum check-in: boleh dibatalkan kapan saja agar PC cepat tersedia lagi.
            $this->showCancelModal = true;
        } else {
            $this->selectedBookingId = null;
            $this->selectedBooking = null;
            session()->flash('error', 'Silakan check-in dengan scan QR sebelum menyelesaikan sesi.');
        }
    }

    public function createBooking()
    {
        app(BookingService::class)->synchronizeExpiredBookings();
        $this->cluster = $this->cluster ? Cluster::find($this->cluster->id) : null;

        $blockReason = $this->bookingBlockReason(Auth::user(), Inventory::find($this->selectedInventoryId));
        if ($blockReason) {
            session()->flash('error', $blockReason);
            $this->showBookModal = false;
            return;
        }

        $this->validate([
            'filterDate' => ['required', 'date', 'after_or_equal:' . Carbon::now('Asia/Jakarta')->toDateString()],
            'start_hour' => 'required|numeric|min:0|max:23',
            'start_minute' => 'required|numeric|min:0|max:59',
            'end_hour' => 'required|numeric|min:0|max:23',
            'end_minute' => 'required|numeric|min:0|max:59',
        ]);

        $this->start_time = $this->buildDateTimeFromInputs($this->filterDate, $this->start_hour, $this->start_minute);
        $this->end_time = $this->buildDateTimeFromInputs($this->filterDate, $this->end_hour, $this->end_minute, $this->start_time);

        if (!$this->start_time || !$this->end_time) {
            $this->addError('start_hour', 'Pilih jam mulai dan jam selesai terlebih dahulu.');
            return;
        }

        $this->validate([
            'start_time' => 'required|date|after:now',
            'end_time'   => 'required|date|after:start_time',
        ]);

        $start = Carbon::parse($this->start_time);
        $end   = Carbon::parse($this->end_time);
        $diffMinutes = $start->diffInMinutes($end);

        $minAllowed = (int) ($this->cluster->min_duration_minutes ?? 30);
        if ($diffMinutes < $minAllowed) {
            $this->addError('end_time', "Minimal durasi booking: {$minAllowed} menit.");
            return;
        }

        $maxAllowed = (int) ($this->cluster->max_duration_minutes ?? 120);
        if ($diffMinutes > $maxAllowed) {
            $this->addError('end_time', "Maksimal durasi booking: {$maxAllowed} menit.");
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
            $this->reset(['start_time', 'end_time', 'start_hour', 'start_minute', 'end_hour', 'end_minute']);

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

            $this->addError('start_time', $e->getMessage());
        }
    }

    public function cancelBooking()
    {
        $booking = Booking::find($this->selectedBookingId);
        if (!$booking || (int) $booking->user_id !== (int) Auth::id()) return;

        if ($booking->status !== 'booked') {
            session()->flash('error', 'Sesi sudah berjalan, gunakan tombol Selesai.');
            $this->resetModals();
            return;
        }

        $booking->update(['status' => 'cancelled', 'cancel_reason' => 'Dibatalkan oleh pengguna']);
        $this->resetModals();
        session()->flash('success', 'Booking dibatalkan.');
    }

    public function finishBooking()
    {
        $booking = Booking::find($this->selectedBookingId);
        if (!$booking || (int) $booking->user_id !== (int) Auth::id()) return;

        if ($booking->status !== 'active' || $booking->check_in_time === null) {
            $this->resetModals();
            session()->flash('error', 'Sesi hanya dapat diselesaikan setelah check-in dengan scan QR.');
            return;
        }

        $booking->update([
            'status' => 'completed', 
            'end_time' => Carbon::now()
        ]); 
        
        $this->resetModals();
        session()->flash('success', 'Sesi selesai.');
    }

    public function render()
    {
        if (!$this->cluster) {
            return view('livewire.booking-page', [
                'allowedClusters'     => collect(),
                'inventories'         => collect(),
                'bookingsMap'         => [],
                'isRoomLockedByAdmin' => false,
                'isClusterRestricted' => false,
                'lockedInventories'   => [],
                'lockReason'          => '',
                'roomEvents'          => collect(),
            ]);
        }

        app(BookingService::class)->synchronizeExpiredBookings();

        $user = Auth::user();
        $allowedClusters = Cluster::where('is_active', true)
            ->when($user->hasRole('student'), function($q) use ($user) {
                $q->where('type', 'public');
            })
            ->orderBy('name')
            ->get();

        $isAdmin = $user->roles->where('role', 'admin')->isNotEmpty();

        // Unit yang disembunyikan admin ("Ketersediaan Booking" dimatikan) tidak tampil untuk pengguna.
        $inventories = Inventory::with('item')
            ->where('cluster_id', $this->cluster->id)
            ->where('status', '!=', 'broken')
            ->when(!$isAdmin, fn ($query) => $query->where('is_bookable', true))
            ->orderBy('name', 'asc')
            ->get();

        $filterStart = Carbon::parse($this->filterDate)->startOfDay();
        $filterEnd   = Carbon::parse($this->filterDate)->endOfDay();

        // Event ruangan penuh hanya menutup cluster ini pada jam event, bukan sepanjang hari.
        $roomEvents = Booking::query()
            ->where('cluster_id', $this->cluster->id)
            ->whereNull('inventory_id')
            ->whereIn('status', ['booked', 'active'])
            ->where('start_time', '<', $filterEnd)
            ->where('end_time', '>', $filterStart)
            ->orderBy('start_time')
            ->get();

        $todayBookings = Booking::where('cluster_id', $this->cluster->id)
            ->whereIn('status', ['booked', 'active'])
            ->whereNotNull('inventory_id')
            ->where(function($q) use ($filterStart, $filterEnd) {
                 $q->where('start_time', '<', $filterEnd)
                   ->where('end_time', '>', $filterStart);
            })
            ->get();

        $bookingsMap = $todayBookings->groupBy('inventory_id');

        $isRoomLockedByAdmin = false;
        $isClusterRestricted = false;
        $lockReason = '';
        $lockedInventories = [];
        $isDosenOrTA = ((int)$user->ta === 1 || $user->roles->contains('role', 'lecturer'));

        if ($this->cluster->type === 'private' && !$isAdmin && !$isDosenOrTA) {
            $isRoomLockedByAdmin = true;
            $isClusterRestricted = true;
            $lockReason = 'Akses Terbatas: Khusus Mahasiswa Tugas Akhir (TA)';
        }

        if (!$this->cluster->canBeBookedBy($user)) {
            $isRoomLockedByAdmin = true;
            $isClusterRestricted = true;
            $lockReason = 'Cluster ini hanya dapat dibooking oleh mahasiswa yang sudah mengambil TA.';
        }

        if ($isRoomLockedByAdmin) {
            $lockedInventories = $inventories->pluck('id')->toArray();
        }

        return view('livewire.booking-page', [
            'allowedClusters'     => $allowedClusters,
            'inventories'         => $inventories,
            'bookingsMap'         => $bookingsMap,
            'isRoomLockedByAdmin' => $isRoomLockedByAdmin,
            'isClusterRestricted' => $isClusterRestricted,
            'lockedInventories'   => $lockedInventories,
            'lockReason'          => $lockReason,
            'roomEvents'          => $roomEvents,
            'currentClusterName'  => $this->cluster->name,
            'currentUserId'       => $user->id,
            'filterDate'          => $this->filterDate,
        ]);
    }
}