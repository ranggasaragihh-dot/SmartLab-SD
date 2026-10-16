<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LabController;
use App\Http\Controllers\Api\PcStatusController;

Route::post('/check-in', [LabController::class, 'checkIn']);

Route::get('pc-status/{id}', [PcStatusController::class, 'checkStatus']);