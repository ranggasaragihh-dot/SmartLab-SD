<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Cluster;
use App\Models\Inventory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function expireUnattendedBookings(?Carbon $now = null): int
    {
        $now ??= Carbon::now();
        $cancelledCount = 0;

        foreach (Cluster::query()->get(['id', 'delay_time']) as $cluster) {
            $graceMinutes = (int) ($cluster->delay_time ?? 15);
            $deadline = $now->copy()->subMinutes($graceMinutes);

            $cancelledCount += Booking::query()
                ->where('cluster_id', $cluster->id)
                ->where('status', 'booked')
                ->whereNull('check_in_time')
                ->where('start_time', '<=', $deadline)
                ->update([
                    'status' => 'cancelled',
                    'cancel_reason' => 'Tidak melakukan check in',
                    'updated_at' => $now,
                ]);
        }

        return $cancelledCount;
    }

    public function completeExpiredSessions(?Carbon $now = null): int
    {
        $now ??= Carbon::now();

        return Booking::query()
            ->where('status', 'active')
            ->where('end_time', '<=', $now)
            ->update([
                'status' => 'completed',
                'updated_at' => $now,
            ]);
    }

    public function synchronizeExpiredBookings(?Carbon $now = null): array
    {
        $now ??= Carbon::now();

        return [
            'cancelled' => $this->expireUnattendedBookings($now),
            'completed' => $this->completeExpiredSessions($now),
        ];
    }

    public function isSlotAvailable($inventoryId, $clusterId, $startTime, $endTime)
    {
        return ! Booking::query()
            ->whereNotIn('status', ['cancelled', 'rejected', 'completed'])
            ->where(function ($query) use ($inventoryId, $clusterId) {
                if ($inventoryId !== null) {
                    $query->where('inventory_id', $inventoryId)
                        ->orWhere(function ($subquery) use ($clusterId) {
                            $subquery->where('cluster_id', $clusterId)
                                ->whereNull('inventory_id');
                        });
                } else {
                    $query->where('cluster_id', $clusterId);
                }
            })
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    public function userHasOverlappingBooking($userId, $startTime, $endTime): bool
    {
        return Booking::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['booked', 'active'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    public function createBooking($userId, $inventoryId, $clusterId, $start, $end)
    {
        return DB::transaction(function () use ($userId, $inventoryId, $clusterId, $start, $end) {
            $cluster = Cluster::query()
                ->whereKey($clusterId)
                ->lockForUpdate()
                ->firstOrFail();

            $user = User::findOrFail($userId);
            if (!$cluster->canBeBookedBy($user)) {
                throw new Exception('Cluster ini hanya dapat dibooking oleh mahasiswa yang sudah mengambil TA.');
            }

            if ($inventoryId !== null) {
                $inventory = Inventory::query()
                    ->whereKey($inventoryId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $inventory->cluster_id !== (int) $cluster->id) {
                    throw new Exception('Unit tidak berada di cluster ini.');
                }
                if (in_array($inventory->status, ['broken', 'maintenance']) || !$inventory->is_bookable) {
                    throw new Exception('Unit ini sedang tidak dapat digunakan.');
                }
            }

            if ($this->userHasOverlappingBooking($userId, $start, $end)) {
                throw new Exception('Anda sudah punya booking lain di jam tersebut. Satu orang hanya dapat memakai satu PC dalam waktu yang sama.');
            }

            if (!$this->isSlotAvailable($inventoryId, $clusterId, $start, $end)) {
                throw new Exception("Gagal! Jadwal bentrok dengan pengguna lain atau ruangan sedang digunakan.");
            }

            return Booking::create([
                'user_id' => $userId,
                'cluster_id' => $clusterId,
                'inventory_id' => $inventoryId,
                'start_time' => $start,
                'end_time' => $end,
                'status' => 'booked',
            ]);
        });
    }

    public function processCheckIn($userId, $qrToken)
    {
        $now = Carbon::now();
        $this->synchronizeExpiredBookings($now);
        $pc = Inventory::where('qr_token', $qrToken)->first();
        if (!$pc) {
            throw new Exception("QR Code tidak valid.");
        }

        $booking = Booking::with('cluster')
            ->where('user_id', $userId)
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
            ->first(function ($booking) use ($now) {
                $startTime = Carbon::parse($booking->start_time);
                $endTime = Carbon::parse($booking->end_time);
                $earlyCheckInMinutes = (int) ($booking->cluster->early_checkin_minutes ?? 10);

                return $now->greaterThanOrEqualTo($startTime->copy()->subMinutes($earlyCheckInMinutes))
                    && $now->lt($endTime);
            });

        if (!$booking) {
            throw new Exception('QR tidak sesuai dengan reservasi aktif Anda, atau pemindaian dilakukan di luar rentang waktu check-in.');
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

        if (!$booking || $booking->status !== 'active' || (int) $booking->user_id !== (int) $userId) {
            throw new Exception('Akses ditolak atau booking tidak aktif.');
        }

        $booking->update(['status' => 'completed']);
        return $booking;
    }

    public function getPcCommand($inventoryId): array
    {
        $inventory = Inventory::find($inventoryId);

        if (!$inventory) {
            return [
                'command' => 'IGNORE',
                'message' => 'PC Not Found',
            ];
        }

        $now = Carbon::now();

        $activeBooking = Booking::with('user:id,name')
            ->where('status', 'active')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->where(function ($query) use ($inventory) {
                $query->where('inventory_id', $inventory->id)
                    ->orWhere(function ($subquery) use ($inventory) {
                        $subquery->where('cluster_id', $inventory->cluster_id)
                            ->whereNull('inventory_id');
                    });
            })
            ->orderBy('end_time')
            ->first();

        return [
            'command' => $activeBooking ? 'UNLOCK' : 'LOCK',
            'user' => $activeBooking?->user?->name,
            'end_time' => $activeBooking?->end_time?->format('Y-m-d H:i:s'),
            'server_time' => $now->format('Y-m-d H:i:s'),
        ];
    }
}