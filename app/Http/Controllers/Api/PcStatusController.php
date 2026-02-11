<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PcStatusController extends Controller
{
    /**
     * Endpoint 1: Cek Status Lock/Unlock
     */
    public function checkStatus($id)
    {
        // Fail-fast validation
        if (!Inventory::where('id', $id)->exists()) {
            return response()->json(['command' => 'IGNORE', 'message' => 'PC Not Found'], 404);
        }

        // Logic Booking (DRY: Logic query bisa dipindah ke Service jika makin rumit, tapi ini cukup)
        $activeBooking = Booking::where('inventory_id', $id)
            ->where('status', 'active') // Sudah Check-in
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

    /**
     * Endpoint 2: Update Spesifikasi Hardware
     */
    public function updateSpecs(Request $request, $id)
    {
        // Validasi input minimal
        if (!$request->isJson()) {
            return response()->json(['message' => 'Invalid Format'], 400);
        }

        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json(['message' => 'PC ID tidak ditemukan di Database'], 404);
        }

        try {
            // 2. Ambil data mentah dari Python
            $specsData = $request->all();

            // Cari atau Buat baru (updateOrCreate adalah implementasi DRY yang bagus)
            // Kita cari berdasarkan inventory_id, jika tidak ada, buat baru.
            $item = InventoryItem::updateOrCreate(
                ['inventory_id' => $id], // Kriteria pencarian
                ['specifications' => $request->all()] // Data yang diupdate/insert
            );

            return response()->json([
                'message' => 'Specs synced successfully',
                'data' => $item->specs
            ]);
        } catch (\Exception $e) {
            // 4. TANGKAP ERRORNYA (Agar kita tahu kenapa 500)
            // Log error ke file storage/logs/laravel.log
            \Illuminate\Support\Facades\Log::error("Gagal Simpan Specs PC $id: " . $e->getMessage());

            return response()->json([
                'message' => 'Server Error (500)',
                'error' => $e->getMessage() // Kirim pesan error asli ke Python biar terbaca
            ], 500);
        }
    }
}