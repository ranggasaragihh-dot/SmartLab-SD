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
        $now = \Illuminate\Support\Carbon::now();
        $this->info("⏳ Cek status booking pada: " . $now->toDateTimeString());

        $noShowBookings = Booking::select('bookings.*')
            ->join('clusters', 'bookings.cluster_id', '=', 'clusters.id')
            ->where('bookings.status', 'booked')
            ->whereRaw('DATE_ADD(bookings.start_time, INTERVAL COALESCE(clusters.delay_time, 15) MINUTE) <= ?', [$now])
            ->get();

        foreach ($noShowBookings as $booking) {
            $booking->update(['status' => 'cancelled']);
            $this->info("❌ Booking ID {$booking->id} HANGUS (Melewati batas toleransi lab).");
        }

        $expiredBookings = Booking::where('status', 'active')
            ->where('end_time', '<=', $now)
            ->get();

        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => 'completed']);
            $this->info("✅ Booking ID {$booking->id} SELESAI (Waktu Habis).");
        }
    }
}
