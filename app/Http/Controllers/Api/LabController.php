<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Services\BookingService;
use Exception;

class LabController extends Controller
{
    protected $bookingService;

    // Dependency Injection: Laravel otomatis masukkan Service ke sini
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Endpoint 1: SCAN QR (Check-In)
     * Dipanggil oleh HP Mahasiswa
     * Method: POST
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'qr_token' => 'required', // Token dari tabel Inventory
        ]);

        try {
            $booking = $this->bookingService->processCheckIn(
                $request->user_id, 
                $request->qr_token
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-in Berhasil! PC Unlocked.',
                'data' => $booking
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() // Misal: "Belum waktunya"
            ], 400);
        }
    }

    /**
     * Endpoint 2: HEARTBEAT PC (Status Monitor)
     * Dipanggil oleh Script Python di PC tiap 5 detik
     * Method: GET
     */
    public function getPcStatus($inventoryId)
    {
        // Langsung tanya Service: "Saya harus ngapain? LOCK atau UNLOCK?"
        $response = $this->bookingService->getPcCommand($inventoryId);
        
        return response()->json($response);
    }
}
