<?php

namespace App\Livewire;

use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\Booking;
use App\Services\BookingService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Exception;

#[Layout('layouts.booking')]
class BookingPage extends Component
{
    // Filter
    public $selectedClusterId = null;
    public $filterDate;
    public $filterTime;

    // Modal State
    public $showBookModal = false;
    public $showCancelModal = false;
    
    // Data Form Booking
    public $selectedInventoryId = null;
    public $selectedInventoryName = '';
    public $selectedBookingId = null; // ID booking untuk proses cancel
    public $selectedBookingStartTime = null;

    public $cluster; 

    public $clusterId;
    public $currentClusterName;

    public $selectedBooking;
    
    public $start_time;
    public $end_time;

    public $bookingStatus = null;

    public function mount($clusterId = null)
    {
        if ($clusterId) {
            // SKENARIO 1: User klik link spesifik (ada ID)
            $this->cluster = Cluster::findOrFail($clusterId);
        } else {
            // SKENARIO 2: User baru buka menu Booking (URL polos)
            // Ambil Cluster pertama yang ada di DB sebagai default
            $this->cluster = Cluster::first();

            // Safety Check: Kalau database kosong melompong (belum ada cluster sama sekali)
            if (!$this->cluster) {
                abort(404, 'Belum ada data Cluster/Lab yang dibuat.');
            }
        }

        // Set properti lainnya berdasarkan cluster yang terpilih
        $this->clusterId = $this->cluster->id;
        $this->currentClusterName = $this->cluster->name;

        // Default pilih lab pertama
        $firstCluster = Cluster::where('is_active', true)->first();
        if ($firstCluster) {
            $this->selectedClusterId = $firstCluster->id;
        }

        $this->filterDate = Carbon::today()->format('Y-m-d');
        $this->filterTime = Carbon::today()->format('H:i');
    }

    // --- LOGIC 1: FILTER LAB BERDASARKAN ROLE USER ---
    private function getAllowedClustersQuery()
    {
        $user = Auth::user();
        $query = Cluster::where('is_active', true);

        // Jika user adalah MAHASISWA (student), hanya tampilkan Lab PUBLIC
        // Asumsi role di database: 'student', 'dosen', 'admin'
        if ($user->role === 'student') {
            $query->where('type', 'public');
        }
        
        // Jika user adalah DOSEN atau ADMIN, tampilkan SEMUA (Public + Private)
        // Jadi tidak perlu 'where' tambahan.

        return $query;
    }

    // Fungsi: Saat kotak PC diklik
    public function clickSeat($inventoryId)
    {
        // 1. Cari data Inventory (PC/Meja)
        $inventory = Inventory::find($inventoryId);
        
        // Cek validasi dasar (jika barang dihapus/maintenance saat user sedang melihat)
        if (!$inventory || $inventory->status !== 'available') {
            return;
        }

        $this->selectedInventoryId = $inventory->id;
        $this->selectedInventoryName = $inventory->name;

        // 2. Cek apakah kursi ini SEDANG ADA YANG PUNYA saat ini?
        // Kita cek manual di sini karena ini untuk kebutuhan UI (Buka modal mana?)
        $activeBooking = Booking::where('inventory_id', $inventoryId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('end_time', '>', Carbon::now()) // Yang belum selesai
            ->first();

        // --- LOGIKA KEPUTUSAN ---

        if ($activeBooking) {
            // SKENARIO A: SUDAH DIBOOKING
            
            if ($activeBooking->user_id === Auth::id()) {
                // Milik Saya -> Buka Modal Cancel/Finish
                $this->selectedBookingId = $activeBooking->id;
                $this->bookingStatus = $activeBooking->status;
                $this->selectedInventoryName = $inventory->name;
                $this->showCancelModal = true; 
                $this->showBookModal = false;
                $this->selectedBooking = $activeBooking;
            } else {
                // Milik Orang Lain -> Jangan lakukan apa-apa (atau tampilkan error)
                // User seharusnya melihat warna biru/abu, tapi kalau memaksa klik, kita tolak.
                return;
            }

        } else {
            // SKENARIO B: KOSONG (AVAILABLE)
            // Ini bagian yang sebelumnya mungkin error/hilang
            
            $this->selectedInventoryId = $inventory->id;
            $this->selectedInventoryName = $inventory->name;
            
            // Reset form input jam
            $this->start_time = null;
            $this->end_time = null;

            // BUKA MODAL
            $this->showBookModal = true;
            $this->showCancelModal = false; 
        }
    }

    // Fungsi: Simpan ke Database
    public function createBooking()
    {
        // Validasi Form Standar
        $this->validate([
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'selectedInventoryId' => 'required'
        ]);

        try {
            // PANGGIL SERVICE (OTAK)
            // Kita lempar tanggung jawab cek bentrok & create ke Service
            (new BookingService())->createBooking(
                Auth::id(),
                $this->selectedInventoryId,
                $this->start_time,
                $this->end_time
            );

            // Sukses
            session()->flash('success', 'Booking berhasil! Silakan datang dan Scan QR.');
            $this->showBookModal = false;
            
            // Reset Form
            $this->start_time = null;
            $this->end_time = null;

        } catch (\Exception $e) {
            // Gagal (Misal: Bentrok)
            session()->flash('error', $e->getMessage());
            // Modal tetap terbuka biar user bisa ganti jam
        }
    }

    public function cancelBooking()
    {
        $booking = Booking::find($this->selectedBookingId);

        // Validasi keamanan: Pastikan yang cancel adalah pemilik booking
        if ($booking && $booking->user_id === Auth::id()) {
            $booking->update(['status' => 'cancelled']);
            session()->flash('success', 'Booking berhasil dibatalkan.');
        } else {
            session()->flash('error', 'Gagal membatalkan.');
        }

        $this->showCancelModal = false;
    }

    public function finishBooking()
    {
        try {
            (new BookingService())->finishBooking($this->selectedBookingId, Auth::id());

            session()->flash('success', 'Sesi selesai. Terima kasih!');
            $this->showCancelModal = false;

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function processCheckIn($userId, $qrToken)
    {
        $now = Carbon::now();

        // ... (Logika cari inventory & booking sama seperti sebelumnya) ...
        $booking = Booking::find($this->selectedBookingId);

        // UPDATE DATA
        $booking->update([
            'status' => 'active',       // Kunci Pintu dibuka
            'check_in_time' => $now,    // MENCATAT JAM MASUK (Ide Anda)
        ]);
        
        return $booking;
    }

    public function render()
    {
        // 1. Ambil Clusters (Lab)
        $clusters = $this->getAllowedClustersQuery()
            ->orderBy('name', 'asc') // Opsional: Urutkan nama lab
            ->get();

        // 2. Ambil Inventory (Station/Meja)
        // Filter HANYA yang 'is_bookable' = true (Hasil migrasi tadi)
        $inventories = collect();
        $bookings = collect();
        if ($this->selectedClusterId) {
            $inventories = Inventory::where('cluster_id', $this->selectedClusterId)
                ->where('is_bookable', true) 
                ->orderBy('name', 'asc')
                ->get();
        }

        // 3. Mapping Booking (Visual Denah)
        // Service tidak mengurus UI, jadi query visual tetap di sini agar ringan
        $bookingsMap = [];
        if ($this->selectedClusterId) {
            $bookings = Booking::whereIn('inventory_id', $inventories->pluck('id'))
                ->whereNotIn('status', ['cancelled', 'finished'])
                ->whereDate('start_time', $this->filterDate)
                ->where('end_time', '>', Carbon::now()) // Hanya yang belum lewat
                ->get();

            foreach($bookings as $b) {
                $bookingsMap[$b->inventory_id] = $b->user_id;
            }
        }

        $inventoryCollection = Inventory::where('cluster_id', $this->selectedClusterId)->get();
        // $units = $inventories->keyBy('name');

        $units = \App\Models\Inventory::where('cluster_id', $this->cluster->id)
                ->orderBy('id', 'asc') 
                ->get();

        if ($this->selectedClusterId && $clusters->isNotEmpty()) {
            $foundCluster = $clusters->find($this->selectedClusterId);
            if ($foundCluster) {
                $currentClusterName = $foundCluster->name;
            }
        }

        return view('livewire.booking-page', [
            // 'clusters' => $clusters,
            'clusters' => Cluster::all(),
            'inventories' => $inventories,
            'bookingsMap' => $bookingsMap,
            'bookings' => $bookings,
            'currentUserId' => Auth::id(),
            'currentClusterName' => $clusters->find($this->selectedClusterId)->name ?? 'Pilih Ruangan',
            'units' => $units,
            ]);

        // return view('livewire.booking-page', []);
        // ->layout('layouts.kosong');
    }
}