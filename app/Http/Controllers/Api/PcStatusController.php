<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Services\BookingService;

class PcStatusController extends Controller
{
    /**
     * Cek Status Lock/Unlock
     */
    public function checkStatus(BookingService $bookingService, $id)
    {
        $now = now();
        Inventory::query()
            ->whereKey($id)
            ->where(function ($query) use ($now) {
                $query->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', $now->copy()->subSeconds(30));
            })
            ->update(['last_seen_at' => $now]);

        $result = $bookingService->getPcCommand($id);

        return response()->json($result, $result['command'] === 'IGNORE' ? 404 : 200);
    }
}