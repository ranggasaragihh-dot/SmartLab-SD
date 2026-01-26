<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BookingService;
use Illuminate\Support\Facades\Auth;

class CheckInController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    // Method ini dipanggil saat URL QR dibuka di Browser
    public function handleScan($qrToken)
    {
        try {
            // 1. Proses Check-in pakai Service (Logic-nya sama persis)
            $booking = $this->bookingService->processCheckIn(Auth::id(), $qrToken);

            // 2. Jika sukses, return Tampilan Sukses (Bukan JSON)
            return redirect()->route('booking.index')
                ->with('success', "Check-in Berhasil! PC {$booking->inventory->name} Unlocked.");

        } catch (\Exception $e) {
            // 3. Jika gagal, return pesan error ke dashboard
            dd([
                'Pesan Error' => $e->getMessage(),
                'Baris Error' => $e->getLine(),
                'File Error'  => $e->getFile(),
                'Trace'       => $e->getTraceAsString() // Jejak langkah errornya
            ]);
            // return redirect()->route('booking')
            //     ->with('error', $e->getMessage());
        }
    }
}