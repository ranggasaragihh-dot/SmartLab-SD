<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingService;

class LabController extends Controller
{
    protected $bookingService;

    // Dependency Injection: Laravel otomatis masukkan Service ke sini
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Endpoint: HEARTBEAT PC (Status Monitor)
     * Dipanggil oleh Script Python di PC tiap 5 detik
     * Method: GET
     */
    public function getPcStatus($inventoryId)
    {
        $response = $this->bookingService->getPcCommand($inventoryId);
        
        return response()->json($response);
    }
}
