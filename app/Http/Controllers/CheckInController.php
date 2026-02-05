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

    public function handleScan($qrToken)
    {
        try {
            // 1. Panggil Service
            // Service sekarang mengembalikan ARRAY: ['booking' => ..., 'scanned_pc' => ...]
            $result = $this->bookingService->processCheckIn(Auth::id(), $qrToken);

            // 2. Ekstrak Data
            $booking = $result['booking'];
            $pc = $result['scanned_pc']; // Data PC fisik yang di-scan

            // 3. Buat Pesan Sukses
            // PENTING: Gunakan $pc->name, JANGAN $booking->inventory->name
            // Karena kalau booking ruangan, inventory di booking itu NULL.
            $message = "Check-in Berhasil! PC {$pc->name} Unlocked.";

            // Tambahan info jika ini adalah booking ruangan
            if (!$booking->inventory_id) {
                $message .= " (Akses Full Ruangan)";
            }

            // 4. Redirect Sukses
            return redirect()->route('booking.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            // 5. Error Handling
            // Kembalikan ke halaman booking dengan pesan error, jangan dd() agar user tidak bingung
            return redirect()->route('booking.index')
                ->with('error', 'Gagal Check-in: ' . $e->getMessage());
            
            // Debugging (Uncomment jika butuh)
            /*
            dd([
                'Pesan Error' => $e->getMessage(),
                'Line' => $e->getLine()
            ]);
            */
        }
    }
}