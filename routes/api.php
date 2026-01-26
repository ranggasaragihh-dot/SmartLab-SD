<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LabController;
use App\Http\Controllers\Api\PcStatusController;

// 1. Jalur Scan QR (Harusnya pakai auth:sanctum nanti, tapi open dulu utk tes)
Route::post('/check-in', [LabController::class, 'checkIn']);

// 2. Jalur Komunikasi PC (Python Script)
// Route::get('/pc-status/{inventoryId}', [LabController::class, 'getPcStatus']);

// Route::prefix('pc')->group(function () {
    // GET: Python tanya "Saya harus ngunci atau buka?"
    Route::get('pc-status/{id}', [PcStatusController::class, 'checkStatus']);

    // POST: Python lapor "Ini spek hardware saya"
    Route::post('pc-specs/{id}', [PcStatusController::class, 'updateSpecs']);
// });