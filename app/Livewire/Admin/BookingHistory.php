<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Booking;
use App\Services\BookingService;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class BookingHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterDate = '';

    public $showDetailSidebar = false;
    public $selectedBooking = null;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }

    public function openDetailSidebar($id)
    {
        $this->selectedBooking = Booking::with(['user', 'inventory.cluster', 'inventory.item.category'])->find($id);
        
        if ($this->selectedBooking) {
            $this->showDetailSidebar = true;
        }
    }

    public function render()
    {
        app(BookingService::class)->synchronizeExpiredBookings();

        $searchTerm = '%' . $this->search . '%';

        $query = Booking::with(['user', 'inventory.item.category', 'inventory.cluster'])
            ->when($this->search, function($q) use ($searchTerm) {
                $q->where(function($sub) use ($searchTerm) {
                    $sub->where('id', 'like', $searchTerm)
                        ->orWhereRaw("CASE WHEN inventory_id IS NULL THEN 'event' ELSE '' END LIKE ?", [$searchTerm])
                        ->orWhereHas('cluster', fn ($c) => $c->where('name', 'like', $searchTerm))
                        ->orWhereHas('user', function($u) use ($searchTerm) {
                            $u->where('name', 'like', $searchTerm)
                            ->orWhere('nomor_induk', 'like', $searchTerm);
                        })

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
            ->when($this->filterStatus, function($q) {
                $q->where('status', $this->filterStatus);
            })
            ->when($this->filterDate, function($q) {
                $q->whereDate('start_time', $this->filterDate);
            })
            ->latest('start_time');

        return view('livewire.admin.booking-history', [
            'bookings' => $query->paginate(15)
        ]);
    }
}