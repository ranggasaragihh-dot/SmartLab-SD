<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckInController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function handleScan($qrToken)
    {
        try {
            $result = $this->bookingService->processCheckIn(Auth::id(), $qrToken);
            $booking = $result['booking'];

            $message = Carbon::now()->lt($booking->start_time)
                ? 'Check-in berhasil. PC akan terbuka pukul ' . $booking->start_time->format('H:i') . '.'
                : 'Check-in berhasil. PC terbuka.';
            if (!$booking->inventory_id) {
                $message .= " (Akses Full Ruangan)";
            }

            return redirect()->route('booking.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('booking.index')
                ->with('error', 'Gagal Check-in: ' . $e->getMessage());
        }
    }
}