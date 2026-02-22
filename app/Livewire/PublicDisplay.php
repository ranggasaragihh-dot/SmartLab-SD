<?php

namespace App\Livewire;

use App\Models\Cluster;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PublicDisplay extends Component
{
    #[Layout('layouts.blank')]
    public function render()
    {
        $now = Carbon::now();

        $clusters = Cluster::with([
            'inventories' => function($q) use ($now) {
                $q->with(['bookings' => function($b) use ($now) {
                    $b->whereIn('status', ['booked', 'active'])
                      ->where('start_time', '<=', $now)
                      ->where('end_time', '>', $now);
                }]);
            },
            'bookings' => function($b) use ($now) {
                $b->whereNull('inventory_id')
                  ->whereIn('status', ['booked', 'active'])
                  ->where('start_time', '<=', $now)
                  ->where('end_time', '>', $now);
            }
        ])->get();

        $globalEvent = null;

        $clusters->map(function ($cluster) use (&$globalEvent) {
            $sortedInventory = $cluster->inventories->sortBy('name', SORT_NATURAL)->values();
            $cluster->setRelation('inventory', $sortedInventory);
            $cluster->active_room_booking = $cluster->bookings->first();

            if (!$globalEvent && $cluster->active_room_booking) {
                $globalEvent = $cluster->active_room_booking;
            }

            return $cluster;
        });

        return view('livewire.public-display', [
            'clusters'    => $clusters,
            'globalEvent' => $globalEvent
        ]);
    }
}