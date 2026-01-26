<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\Booking;

class UpdateBookingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis update status booking';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info("⏳ Cek status booking pada: " . $now->toDateTimeString());

        // 1. ATURAN NO-SHOW (15 MENIT)
        // Cari booking berstatus 'booked' (belum check-in)
        // Yang waktu mulainya sudah lewat 15 menit yang lalu
        $limitTime = $now->copy()->subMinutes(15);

        $noShowBookings = Booking::where('status', 'booked') // Asumsi 'booked' = belum scan
            ->where('start_time', '<=', $limitTime)
            ->get();

        foreach ($noShowBookings as $booking) {
            // Batalkan otomatis agar PC bisa dipakai orang lain
            $booking->update(['status' => 'cancelled']);
            $this->info("❌ Booking ID {$booking->id} HANGUS (No-Show > 15 menit).");
        }

        // 2. AUTO FINISHED (JIKA WAKTU HABIS)
        // Berlaku untuk yang statusnya 'active' (sedang main) tapi waktunya habis
        $expiredBookings = Booking::where('status', 'active')
            ->where('end_time', '<=', $now)
            ->get();

        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => 'completed']);
            $this->info("✅ Booking ID {$booking->id} SELESAI (Waktu Habis).");
        }
    }
}
