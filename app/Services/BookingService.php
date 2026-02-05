<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Inventory;
use Carbon\Carbon;
use Exception;

class BookingService
{
    /**
     * 1. Cek Ketersediaan Slot (Unified Check)
     * Mengecek bentrok PC vs PC, DAN PC vs Ruangan.
     */
    public function isSlotAvailable($inventoryId, $clusterId, $startTime, $endTime)
    {
        // Query Booking yang BENTROK
        return ! Booking::where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->where('status', '!=', 'completed')
            ->where(function ($query) use ($inventoryId, $clusterId) {
                
                // SKENARIO A: Booking Satuan (PC Tertentu)
                if ($inventoryId) {
                    $query->where('inventory_id', $inventoryId)
                          ->orWhere(function($sub) use ($clusterId) {
                              // Cek jika ada yang booking FULL RUANGAN ini
                              $sub->where('cluster_id', $clusterId)
                                  ->whereNull('inventory_id');
                          });
                } 
                // SKENARIO B: Booking Full Ruangan (inventoryId = null)
                else {
                    // Jika mau booking ruangan, pastikan tidak ada SATUPUN PC 
                    // di ruangan itu yang sedang dibooking orang lain.
                    $query->where('cluster_id', $clusterId);
                }
            })
            // Logika Irisan Waktu (Overlap)
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

    /**
     * 2. Membuat Booking Baru
     */
    public function createBooking($userId, $inventoryId, $clusterId, $start, $end)
    {
        // Validasi
        if (!$this->isSlotAvailable($inventoryId, $clusterId, $start, $end)) {
            throw new Exception("Gagal! Jadwal bentrok dengan pengguna lain atau ruangan sedang digunakan.");
        }

        return Booking::create([
            'user_id'      => $userId,
            'cluster_id'   => $clusterId,   // Penting utk tracking ruangan
            'inventory_id' => $inventoryId, // Null jika booking ruangan
            'start_time'   => $start,
            'end_time'     => $end,
            'status'       => 'booked',
        ]);
    }

    /**
     * 3. Proses Check-in (Scan QR) - VERSI UNIFIED
     * Mendukung: Login Personal PC & Login Full Room Access
     */
    public function processCheckIn($userId, $qrToken)
    {
        $now = Carbon::now();

        // A. Cari PC dari QR
        $pc = Inventory::where('qr_token', $qrToken)->first();
        if (!$pc) {
            throw new Exception("QR Code tidak valid.");
        }

        // B. Cari Booking yang Relevan (Inheritance Logic)
        // Kita cari booking milik user ini, entah itu booking PC ini ATAU booking Ruangannya.
        $booking = Booking::where('user_id', $userId)
            ->whereIn('status', ['booked', 'active'])
            ->where(function ($query) use ($pc) {
                // 1. Cek Booking Spesifik PC ini
                $query->where('inventory_id', $pc->id)
                // 2. ATAU Cek Booking Full Ruangan (Cluster PC ini)
                      ->orWhere(function($sub) use ($pc) {
                          $sub->where('cluster_id', $pc->cluster_id)
                              ->whereNull('inventory_id');
                      });
            })
            // Urutkan biar yang paling mendekati waktu sekarang yang diambil
            ->orderBy('start_time', 'asc')
            ->get()
            // Filter manual waktu di PHP agar bisa kasih pesan error spesifik
            // Cari booking yang jadwalnya "masuk akal" (sedang berjalan atau akan segera mulai)
            ->filter(function ($b) use ($now) {
                // Ambil booking jika belum berakhir
                return $now->lt(Carbon::parse($b->end_time));
            })
            ->first();

        // C. Validasi Hasil Pencarian
        if (!$booking) {
            throw new Exception("Anda tidak memiliki jadwal di PC/Ruangan ini, atau jadwal sudah berakhir.");
        }

        $startTime = Carbon::parse($booking->start_time);
        
        // D. Cek Kecepatan (Toleransi 15 Menit)
        if ($now->lessThan($startTime->copy()->subMinutes(15))) {
            $waitMinutes = $now->diffInMinutes($startTime);
            throw new Exception("Terlalu cepat! Jadwal Anda baru mulai {$waitMinutes} menit lagi.");
        }

        // E. Update Status (Check-in)
        if ($booking->status === 'booked') {
            $booking->update([
                'status' => 'active',
                'check_in_time' => $now
            ]);
        }

        // F. Return Array (PENTING: Kembalikan Booking + Data PC)
        return [
            'booking' => $booking,
            'scanned_pc' => $pc // Data PC fisik yang di-scan
        ];
    }

    /**
     * 4. Selesaikan Booking
     */
    public function finishBooking($bookingId, $userId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking || $booking->user_id !== $userId) {
            throw new Exception("Akses ditolak.");
        }

        $booking->update(['status' => 'completed']);
        return $booking;
    }

    /**
     * 5. Perintah untuk Client PC (Python)
     * Menentukan apakah PC harus LOCK atau UNLOCK
     */
    public function getPcCommand($inventoryId)
    {
        $inventory = Inventory::find($inventoryId);
        
        if (!$inventory || empty($inventory->mac_address)) {
            return ['command' => 'IGNORE']; 
        }

        $now = Carbon::now();

        // Cek Session Aktif (Unified Check)
        $hasActiveSession = Booking::where('status', 'active')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->where(function($q) use ($inventory) {
                // PC ini dibuka jika:
                // 1. Ada yang booking PC ini secara langsung
                $q->where('inventory_id', $inventory->id)
                // 2. ATAU Ada yang booking Ruangannya (Cluster)
                  ->orWhere(function($sub) use ($inventory) {
                      $sub->where('cluster_id', $inventory->cluster_id)
                          ->whereNull('inventory_id');
                  });
            })
            ->exists();

        return $hasActiveSession ? ['command' => 'UNLOCK'] : ['command' => 'LOCK'];
    }
}