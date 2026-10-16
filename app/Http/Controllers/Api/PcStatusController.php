<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingService;

class PcStatusController extends Controller
{
    /**
     * Cek Status Lock/Unlock
     */
    public function checkStatus(BookingService $bookingService, $id)
    {
        $result = $bookingService->getPcCommand($id);

        return response()->json($result, $result['command'] === 'IGNORE' ? 404 : 200);
    }
}