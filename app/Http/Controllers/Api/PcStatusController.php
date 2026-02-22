<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Booking;
use Carbon\Carbon;

class PcStatusController extends Controller
{
    /**
     * Cek Status Lock/Unlock
     */
    public function checkStatus($id)
    {
        if (!Inventory::where('id', $id)->exists()) {
            return response()->json(['command' => 'IGNORE', 'message' => 'PC Not Found'], 404);
        }

        $activeBooking = Booking::where('inventory_id', $id)
            ->where('status', 'active')
            ->where('start_time', '<=', Carbon::now())
            ->where('end_time', '>', Carbon::now())
            ->first();

        if ($activeBooking) {
            return response()->json([
                'command' => 'UNLOCK',
                'user' => $activeBooking->user->name,
                'end_time' => \Carbon\Carbon::parse($activeBooking->end_time)->format('Y-m-d H:i:s'),
            ]);
        }

        return response()->json(['command' => 'LOCK', 'user' => null]);
    }
}