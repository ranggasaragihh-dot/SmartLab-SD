<?php

namespace App\Livewire;

use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Booking;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB; // Tambahan untuk Transaction

#[Layout('layouts.booking')]
class BookingPage extends Component
{
    // --- STATE UTAMA ---
    public $selectedClusterId = null;
    public $cluster;

    // --- FILTER & FORM ---
    public $filterDate; // Format: Y-m-d
    public $filterTime; // Hapus jika tidak dipakai di UI user (User cuma butuh Available/Booked)
    
    // Form Input Booking
    public $start_time;
    public $end_time;
    
    // --- DATA SELECTION ---
    public $selectedInventoryId = null; 
    public $selectedInventoryName = '';
    
    // --- MODAL STATE ---
    public $showBookModal = false;
    public $showCancelModal = false;
    public $showFinishModal = false;
    
    // --- ACTION STATE ---
    public $selectedBookingId = null;
    public $selectedBooking;
    // public $bookingStatus = null; // Tidak krusial disimpan sebagai public property

    public function mount($clusterId = null)
    {
        // PERBAIKAN 1: Default Filter Date yang Logis
        // Jika user buka halaman, defaultnya tanggal hari ini (tanpa jam, karena filter biasanya per hari)
        // $this->filterDate = Carbon::today('Asia/Jakarta')->format('Y-m-d\TH:i'); 
        $this->filterDate = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m-d\TH:i');
        $this->filterTime = Carbon::now()->format('H:i');

        // Default Cluster logic
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

    // =================================================================
    // 1. FUNGSI INTERAKSI (CLICK SEAT)
    // =================================================================

    public function clickSeat($inventoryId)
    {
        $this->resetModals();

        // Pastikan cluster ter-load
        if (!$this->cluster && $this->selectedClusterId) {
            $this->cluster = Cluster::find($this->selectedClusterId);
        }

        $inventory = Inventory::find($inventoryId);
        
        // 1. Validasi Fisik
        if (!$inventory || in_array($inventory->status, ['broken', 'maintenance'])) {
            return; 
        }

        $this->selectedInventoryId = $inventory->id;
        $this->selectedInventoryName = $inventory->name;

        // 2. Cek Booking Aktif Milik USER SENDIRI (Untuk Check-in/Finish/Cancel)
        // Logika ini BENAR menggunakan Carbon::now() karena urusannya sesi yang sedang berjalan
        $myActiveBooking = Booking::where('inventory_id', $inventoryId)
            ->where('user_id', Auth::id())
            ->whereNotIn('status', ['cancelled', 'rejected', 'completed'])
            ->where('end_time', '>', Carbon::now()) // Masih berjalan
            ->orderBy('start_time', 'asc')
            ->first();

        if ($myActiveBooking) {
            $this->handleMyBookingInteraction($myActiveBooking);
            return;
        }

        // 3. Logika Membuka Form Booking Baru
        // PERBAIKAN 2: Jangan validasi 'Available' di sini secara ketat terhadap Waktu Sekarang.
        // User mungkin mengklik kursi kosong di masa depan.
        // Cukup cek hak akses (Private/TA).

        $user = Auth::user();
        
        // Standarisasi akses role (Pastikan kolom di DB konsisten, misal 'role' atau 'roles')
        // Asumsi: kolom DB adalah 'role' (singular) sesuai Laravel convention standar
        $isLecturer = ($user->role === 'lecture'); 
        $isStudentTA = ((int)$user->ta === 1); 

        // Validasi Akses Cluster Private
        if ($this->cluster->type === 'private' && !($isLecturer || $isStudentTA)) {
            session()->flash('error', 'Cluster ini khusus Mahasiswa TA.');
            return;
        }

        // Validasi Akses Inventory Private
        if ($inventory->type === 'private' && !$isStudentTA && $user->role !== 'admin') {
            session()->flash('error', 'Unit ini khusus Mahasiswa TA.');
            return;
        }

        // Jika lolos, buka modal booking
        // Set default jam start/end berdasarkan filterDate user + jam sekarang (atau 08:00)
        $baseDate = $this->filterDate ? Carbon::parse($this->filterDate) : Carbon::today();
        
        // UX: Jika filter hari ini, set start jam sekarang. Jika besok, set jam 08:00
        if ($baseDate->isToday()) {
            $defaultStart = Carbon::now()->addMinutes(5)->format('Y-m-d\TH:i'); // +5 menit buffer
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

        // Jika sudah aktif atau waktu mulai sudah lewat -> Harus Finish
        if ($booking->status === 'active' || $now->gte($startTime)) {
            $this->showFinishModal = true;
        } else {
            // Masih status booked dan belum mulai -> Boleh Cancel
            $this->showCancelModal = true;
        }
    }

    // =================================================================
    // 2. FUNGSI TRANSAKSI (CREATE)
    // =================================================================

    public function createBooking()
    {
        $this->validate([
            'start_time' => 'required|date|after:now',
            'end_time'   => 'required|date|after:start_time',
        ]);

        $start = Carbon::parse($this->start_time);
        $end   = Carbon::parse($this->end_time);
        $diffMinutes = $start->diffInMinutes($end);

        // Validasi Durasi
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

        // PERBAIKAN 3: Gunakan Transaction & Double Check
        DB::beginTransaction();

        try {
            // Cek ketersediaan di dalam transaction untuk meminimalisir race condition
            // (Meski idealnya pakai lockForUpdate, tapi logic isSlotAvailable cukup kompleks)
            if (!$this->isSlotAvailable($start, $end)) {
                DB::rollBack();
                $this->addError('start_time', 'Slot baru saja diambil orang lain atau tidak tersedia.');
                return;
            }

            Booking::create([
                'user_id'      => Auth::id(),
                'cluster_id'   => $this->cluster->id,
                'inventory_id' => $this->selectedInventoryId,
                'start_time'   => $this->start_time,
                'end_time'     => $this->end_time,
                'status'       => 'booked',
            ]);

            DB::commit();

            session()->flash('success', 'Booking berhasil!');
            $this->showBookModal = false;
            $this->reset(['start_time', 'end_time']);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // =================================================================
    // 3. ACTION LAINNYA
    // =================================================================

    public function cancelBooking()
    {
        // Logic Cancel tetap valid (Validation Owner & Time)
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
            'end_time' => Carbon::now() // Update end_time real saat finish dini
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
            // Refresh modal state jika perlu, atau tutup modal
            $this->resetModals(); 
        }
    }

    // =================================================================
    // 4. HELPER LOGIC
    // =================================================================

    private function isSlotAvailable($start, $end)
    {
        // Logic isSlotAvailable Anda sudah cukup bagus (memperhitungkan delay & global lock).
        // Tidak ada perubahan signifikan yang diperlukan di sini kecuali konsistensi 'role'.
        
        $user = Auth::user();
        $delay = (int)($this->cluster->delay_time_minutes ?? 0);
        
        $startWithBuffer = $start->copy()->subMinutes($delay);
        $endWithBuffer = $end->copy()->addMinutes($delay);

        return ! Booking::where('cluster_id', $this->cluster->id)
            ->whereIn('status', ['booked', 'active'])
            ->where(function ($query) use ($user) {
                // Cek Inventory Spesifik
                $query->where('inventory_id', $this->selectedInventoryId); 
                
                // Cek Global Lock
                $isImmune = ($this->cluster->type === 'private' && (int)$user->ta === 1);
                if (!$isImmune) {
                    $query->orWhereNull('inventory_id'); 
                }
            })
            ->where(function ($q) use ($startWithBuffer, $endWithBuffer) {
                // Logic Overlap
                $q->where('start_time', '<', $endWithBuffer)
                  ->where('end_time', '>', $startWithBuffer);
            })
            ->exists();
    }

    // =================================================================
    // 5. RENDER
    // =================================================================

    public function render()
    {
        if (!$this->cluster) {
            return view('livewire.booking-page', [
                'allowedClusters' => collect(),
                'inventories' => collect(),
                'bookingsMap' => [],
                'isRoomLockedByAdmin' => false,
                'lockedInventories' => [],
            ]);
        }

        $user = Auth::user();

        // 1. Ambil List Cluster (Konsistensi Role)
        $allowedClusters = Cluster::where('is_active', true)
            ->when($user->role === 'student', function($q) {
                $q->where('type', 'public');
            })
            ->orderBy('name')
            ->get();

        // 2. Ambil Inventories
        $allInventories = Inventory::where('cluster_id', $this->cluster->id)
            ->orderBy('name', 'asc')
            ->get();

        // 4. Siapkan Data Booking (Transaksi)
        // Logika Waktu: Sepanjang hari dari tanggal yang dipilih
        $inventories = Inventory::where('cluster_id', $this->cluster->id)
            ->where('status', '!=', 'broken') // Broken tidak usah diload
            ->orderBy('name', 'asc')
            ->get();

        // 3. Cek Room Lock (Global) PADA TANGGAL FILTER
        // Masalah di kode lama: Cek lock pakai Carbon::now(), padahal user lihat tanggal besok.
        // Kita harus cek apakah ada Lock Admin yang beririsan dengan Filter Date (sepanjang hari)
        
        $filterStart = Carbon::parse($this->filterDate)->startOfDay();
        $filterEnd   = Carbon::parse($this->filterDate)->endOfDay();

        $globalRoomBooking = Booking::where('cluster_id', $this->cluster->id)
            ->whereNull('inventory_id') // Global Lock
            ->whereIn('status', ['booked', 'active'])
            ->where(function($q) use ($filterStart, $filterEnd) {
                // Cek overlap sepanjang hari tanggal filter
                $q->where('start_time', '<', $filterEnd)
                  ->where('end_time', '>', $filterStart);
            })
            ->first();

        // 4. Mapping Booking Satuan
        $todayBookings = Booking::where('cluster_id', $this->cluster->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('inventory_id')
            ->where(function($q) use ($filterStart, $filterEnd) {
                 // Cek overlap sepanjang hari tanggal filter
                 $q->where('start_time', '<', $filterEnd)
                   ->where('end_time', '>', $filterStart);
            })
            ->get();
        
        // Format Map: [inventory_id => [user_id, status]] agar di frontend bisa diwarnai
        $bookingsMap = [];
        foreach($todayBookings as $b) {
            // Kita simpan array, mungkin nanti butuh info status/jam
            $bookingsMap[$b->inventory_id] = [
                'user_id' => $b->user_id,
                'status' => $b->status
            ];
        }

        // 5. Logic Lock Status UI
        $isRoomLockedByAdmin = false;
        $lockReason = '';
        $lockedInventories = [];

        $isLecturer = ($user->role === 'lecture');
        $isStudentTA = ((int)$user->ta === 1 && $this->cluster->type === 'private');
        $hasAccessRight = $isLecturer || $isStudentTA;

        if ($this->cluster->type === 'private' && !$hasAccessRight) {
            $isRoomLockedByAdmin = true;
            $lockReason = "Khusus Mahasiswa TA";
            $lockedInventories = $inventories->pluck('id')->toArray();
        } 
        else if ($globalRoomBooking && !$hasAccessRight) {
            $isRoomLockedByAdmin = true;
            $lockReason = $globalRoomBooking->purpose ?? 'Kegiatan Lab';
            $lockedInventories = $inventories->pluck('id')->toArray();
        }

        // PERBAIKAN 4: Hapus getClusterData() (Statistik) karena tidak relevan untuk User Booking
        
        return view('livewire.booking-page', [
            'allowedClusters' => $allowedClusters,
            'inventories'     => $inventories,
            'bookingsMap'     => $bookingsMap, // Kirim map yang sudah diperbaiki
            'isRoomLockedByAdmin' => $isRoomLockedByAdmin,
            'lockedInventories'   => $lockedInventories,
            'lockReason'          => $lockReason,
            'currentClusterName'  => $this->cluster->name,
            'currentUserId'       => $user->id,
            'filterDate'          => $this->filterDate, // Kirim balik tanggal filter
        ]);
    }
}