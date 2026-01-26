<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Inventory;
use Carbon\Carbon;


class BookingService
{
    /**
     * Cek apakah inventory tersedia (TIDAK BENTROK).
     * Mengembalikan TRUE jika aman untuk dibooking.
     */
    public function isSlotAvailable($inventoryId, $startTime, $endTime)
    {
        // Cek apakah ada booking lain yang statusnya MASIH AKTIF
        // (Bukan cancelled, rejected, atau finished)
        $conflictingBooking = Booking::where('inventory_id', $inventoryId)
            ->whereNotIn('status', ['cancelled', 'rejected', 'completed'])
            ->where(function ($query) use ($startTime, $endTime) {
                // Logika Irisan Waktu (Time Overlap)
                // Jadwal baru dianggap bentrok jika:
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($sub) use ($startTime, $endTime) {
                         $sub->where('start_time', '<', $startTime)
                             ->where('end_time', '>', $endTime);
                    });
            })->exists();

        // Jika ada bentrok, berarti TIDAK Available (False)
        return !$conflictingBooking;
    }

    /**
     * Membuat Booking Baru
     */
    public function createBooking($userId, $inventoryId, $start, $end)
    {
        // 1. Validasi Ketersediaan (Panggil fungsi di atas)
        if (!$this->isSlotAvailable($inventoryId, $start, $end)) {
            throw new \Exception("Gagal! Jadwal bentrok dengan pengguna lain.");
        }

        // 2. Buat Data
        return Booking::create([
            'user_id' => $userId,
            'inventory_id' => $inventoryId,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'booked', // Status awal: MENUNGGU CHECK-IN
        ]);
    }

    /**
     * Proses Check-in (Scan QR)
     * Mengubah status BOOKED -> ACTIVE
     */
    public function processCheckIn($userId, $qrToken)
    {
        // 1. Set Waktu Sekarang (Pastikan Timezone Jakarta)
        $now = Carbon::now(); 

        // 2. Cari PC (Inventory)
        $inventory = Inventory::where('qr_token', $qrToken)->first();
        if (!$inventory) {
            throw new \Exception("QR Code tidak valid.");
        }

        // 3. CARI BOOKING MILIK USER DI PC INI (Tanpa filter jam dulu)
        // Ambil booking yang statusnya 'booked' milik user ini di PC ini
        $booking = Booking::where('inventory_id', $inventory->id)
            ->where('user_id', $userId)
            ->where('status', 'booked')
            ->latest() // Ambil yang paling baru dibuat
            ->first();

        // --- LOGIC VALIDASI WAKTU ---
        
        // A. Jika booking sama sekali tidak ditemukan
        if (!$booking) {
            throw new \Exception("Anda tidak memiliki jadwal booking aktif di PC ini.");
        }

        $startTime = Carbon::parse($booking->start_time);
        $endTime = Carbon::parse($booking->end_time);
        
        // B. Cek apakah Datang Kepagian (Lebih dari 15 menit sebelum mulai)
        // Toleransi: Boleh masuk 15 menit sebelum jam mulai
        if ($now->lessThan($startTime->copy()->subMinutes(15))) {
            $waitMinutes = $now->diffInMinutes($startTime);
            throw new \Exception("Terlalu cepat! Jadwal Anda mulai {$waitMinutes} menit lagi.");
        }

        // C. Cek apakah Sudah Lewat (Expired)
        if ($now->greaterThan($endTime)) {
            throw new \Exception("Jadwal booking ini sudah berakhir.");
        }

        // D. SUKSES - Update Data
        $booking->update([
            'status' => 'active', 
            'check_in_time' => $now
        ]);
        
        return $booking;
    }

    /**
     * Selesaikan Booking (Checkout Dini)
     */
    public function finishBooking($bookingId, $userId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking || $booking->user_id !== $userId) {
            throw new \Exception("Akses ditolak.");
        }

        $booking->update(['status' => 'completed']);
        return $booking;
    }

    /**
     * LOGIKA 5: Perintah untuk PC (Python Script)
     */
    public function getPcCommand($inventoryId)
    {
        $inventory = Inventory::find($inventoryId);
        
        // Cek MAC Address (Sesuai ERD) - Jika kosong, abaikan
        if (empty($inventory->mac_address)) {
            return ['command' => 'IGNORE']; 
        }

        $now = Carbon::now();

        // Cek apakah ada yang statusnya ACTIVE sekarang?
        $activeSession = Booking::where('inventory_id', $inventoryId)
            ->where('status', 'active')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->exists();

        return $activeSession ? ['command' => 'UNLOCK'] : ['command' => 'LOCK'];
    }
}