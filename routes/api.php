<?php

use App\Http\Controllers\Api\AuthQrController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for AUTOBOX Hardware Integration (Raspberry Pi / Arduino)
|--------------------------------------------------------------------------
*/

Route::middleware(['hardware.key', 'throttle:60,1'])->group(function () {
    Route::post('/authenticate-qr', [AuthQrController::class, 'authenticate']);
    Route::get('/keys', [AuthQrController::class, 'getKeyStatuses']);
    Route::post('/key-missing', [AuthQrController::class, 'reportMissing']);
    Route::post('/slider-event', [AuthQrController::class, 'reportSliderEvent']);
    Route::get('/offline-cache', [AuthQrController::class, 'getOfflineCache']);
    Route::post('/sync-offline-logs', [AuthQrController::class, 'syncOfflineLogs']);
});

