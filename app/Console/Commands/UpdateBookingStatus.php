<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Services\BookingService;

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
    public function handle(BookingService $bookingService)
    {
        $now = Carbon::now();
        $this->info('Cek status booking pada: ' . $now->toDateTimeString());

        $updatedCounts = $bookingService->synchronizeExpiredBookings($now);
        $this->info("{$updatedCounts['cancelled']} booking no-show dibatalkan.");
        $this->info("{$updatedCounts['completed']} sesi aktif diselesaikan.");
    }
}
