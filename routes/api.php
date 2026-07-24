<?php

use App\Http\Controllers\Api\AuthQrController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for AUTOBOX Hardware Integration (Raspberry Pi / Arduino)
|--------------------------------------------------------------------------
*/

Route::post('/authenticate-qr', [AuthQrController::class, 'authenticate']);
Route::get('/keys', [AuthQrController::class, 'getKeyStatuses']);
Route::post('/key-missing', [AuthQrController::class, 'reportMissing']);
