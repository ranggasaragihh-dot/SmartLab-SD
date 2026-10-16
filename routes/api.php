<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PcStatusController;

Route::get('pc-status/{id}', [PcStatusController::class, 'checkStatus']);