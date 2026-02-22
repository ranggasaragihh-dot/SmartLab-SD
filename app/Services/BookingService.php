<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Inventory;
use Carbon\Carbon;
use Exception;

class BookingService
{
    public function isSlotAvailable($inventoryId, $clusterId, $startTime, $endTime)
    {
        return ! Booking::where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->where('status', '!=', 'completed')
            ->where(function ($query) use ($inventoryId, $clusterId) {
                
                if ($inventoryId) {
                    $query->where('inventory_id', $inventoryId)
                          ->orWhere(function($sub) use ($clusterId) {
                              $sub->where('cluster_id', $clusterId)
                                  ->whereNull('inventory_id');
                          });
                } 
                else {
                    $query->where('cluster_id', $clusterId);
                }
            })
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($sub) use ($startTime, $endTime) {
                       $sub->where('start_time', '<', $startTime)
                           ->where('end_time', '>', $endTime);
                  });
            })
            ->exists();
    }

    public function createBooking($userId, $inventoryId, $clusterId, $start, $end)
    {
        if (!$this->isSlotAvailable($inventoryId, $clusterId, $start, $end)) {
            throw new Exception("Gagal! Jadwal bentrok dengan pengguna lain atau ruangan sedang digunakan.");
        }

        return Booking::create([
            'user_id'      => $userId,
            'cluster_id'   => $clusterId,
            'inventory_id' => $inventoryId,
            'start_time'   => $start,
            'end_time'     => $end,
            'status'       => 'booked',
        ]);
    }

    public function processCheckIn($userId, $qrToken)
    {
        $now = Carbon::now();
        $pc = Inventory::where('qr_token', $qrToken)->first();
        if (!$pc) {
            throw new Exception("QR Code tidak valid.");
        }

        $booking = Booking::where('user_id', $userId)
            ->whereIn('status', ['booked', 'active'])
            ->where(function ($query) use ($pc) {
                $query->where('inventory_id', $pc->id)
                      ->orWhere(function($sub) use ($pc) {
                          $sub->where('cluster_id', $pc->cluster_id)
                              ->whereNull('inventory_id');
                      });
            })
            ->orderBy('start_time', 'asc')
            ->get()
            ->filter(function ($b) use ($now) {
                return $now->lt(Carbon::parse($b->end_time));
            })
            ->first();

        if (!$booking) {
            throw new Exception("Anda tidak memiliki jadwal di PC/Ruangan ini, atau jadwal sudah berakhir.");
        }

        $startTime = Carbon::parse($booking->start_time);
        
        if ($now->lessThan($startTime->copy()->subMinutes(15))) {
            $waitMinutes = $now->diffInMinutes($startTime);
            throw new Exception("Terlalu cepat! Jadwal Anda baru mulai {$waitMinutes} menit lagi.");
        }

        if ($booking->status === 'booked') {
            $booking->update([
                'status' => 'active',
                'check_in_time' => $now
            ]);
        }

        return [
            'booking'    => $booking,
            'scanned_pc' => $pc
        ];
    }

    public function finishBooking($bookingId, $userId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking || $booking->user_id !== $userId) {
            throw new Exception("Akses ditolak.");
        }

        $booking->update(['status' => 'completed']);
        return $booking;
    }

    public function getPcCommand($inventoryId)
    {
        $inventory = Inventory::find($inventoryId);
        
        if (!$inventory || empty($inventory->mac_address)) {
            return ['command' => 'IGNORE']; 
        }

        $now = Carbon::now();

        $hasActiveSession = Booking::where('status', 'active')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->where(function($q) use ($inventory) {
                $q->where('inventory_id', $inventory->id)
                  ->orWhere(function($sub) use ($inventory) {
                      $sub->where('cluster_id', $inventory->cluster_id)
                          ->whereNull('inventory_id');
                  });
            })
            ->exists();

        return $hasActiveSession ? ['command' => 'UNLOCK'] : ['command' => 'LOCK'];
    }
}