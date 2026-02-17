<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Booking;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class BookingHistory extends Component
{
    use WithPagination; // Wajib untuk memecah data

    // 1. STATE FILTERING
    public $search = '';
    public $filterStatus = '';
    public $filterDate = '';

    // 2. STATE SIDEBAR
    public $showDetailSidebar = false;
    public $selectedBooking = null;

    // Reset paginasi ke halaman 1 setiap kali admin mengetik pencarian
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }

    // 3. LOGIKA SIDEBAR DETAIL
    public function openDetailSidebar($id)
    {
        // Eager loading untuk mencegah N+1 Query Problem
        $this->selectedBooking = Booking::with(['user', 'inventory.cluster', 'inventory.item.category'])->find($id);
        
        if ($this->selectedBooking) {
            $this->showDetailSidebar = true;
        }
    }

    // 4. MESIN PENCARIAN & RENDER
    public function render()
    {
        $searchTerm = '%' . $this->search . '%';

        $query = Booking::with(['user', 'inventory.item.category', 'inventory.cluster'])
            ->when($this->search, function($q) use ($searchTerm) {
                $q->where(function($sub) use ($searchTerm) {
                    $sub->where('id', 'like', $searchTerm)
                        
                        // 1. TRICK: Mencari kata "event" pada inventory_id yang NULL
                        // Kita paksa database melihat NULL sebagai tulisan 'event'
                        ->orWhereRaw("CASE WHEN inventory_id IS NULL THEN 'event' ELSE '' END LIKE ?", [$searchTerm])

                        // 2. Cari berdasarkan User
                        ->orWhereHas('user', function($u) use ($searchTerm) {
                            $u->where('name', 'like', $searchTerm)
                            ->orWhere('nomor_induk', 'like', $searchTerm);
                        })

                        // 3. Cari berdasarkan Unit/Meja & Kategori
                        ->orWhereHas('inventory', function($inv) use ($searchTerm) {
                            $inv->where('name', 'like', $searchTerm)
                                ->orWhereHas('cluster', function($c) use ($searchTerm) {
                                    $c->where('name', 'like', $searchTerm);
                                })
                                ->orWhereHas('item.category', function($cat) use ($searchTerm) {
                                    $cat->where('name', 'like', $searchTerm);
                                });
                        });
                });
            })
            // Filter 2: Dropdown Status
            ->when($this->filterStatus, function($q) {
                $q->where('status', $this->filterStatus);
            })
            // Filter 3: Tanggal Spesifik
            ->when($this->filterDate, function($q) {
                $q->whereDate('start_time', $this->filterDate);
            })
            // Urutkan dari transaksi terbaru
            ->latest('start_time');

        // Lemparkan 15 data per halaman ke View
        return view('livewire.admin.booking-history', [
            'bookings' => $query->paginate(15)
        ]); // Pastikan layout admin Anda dipanggil (sesuaikan jika namanya beda)
    }
}