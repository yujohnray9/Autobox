<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KeyController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AccessLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\OfflineRecordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::resource('users', UserController::class);
    Route::get('users/{user}/qr', [UserController::class, 'qr'])->name('users.qr');
    Route::post('users/{user}/regenerate-qr', [UserController::class, 'regenerateQr'])->name('users.regenerate-qr');

    // Keys
    Route::resource('keys', KeyController::class);
    Route::patch('keys/{key}/status', [KeyController::class, 'updateStatus'])->name('keys.update-status');

    // Schedules
    Route::resource('schedules', ScheduleController::class)->except(['edit', 'update', 'show']);

    // Transactions
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/export', [TransactionController::class, 'export'])->name('transactions.export');

    // Access Logs & Cryptographic Integrity Verification
    Route::get('access-logs', [AccessLogController::class, 'index'])->name('access-logs.index');
    Route::get('access-logs/integrity', [AccessLogController::class, 'checkIntegrity'])->name('access-logs.integrity');
    Route::post('access-logs/simulate-tamper', [AccessLogController::class, 'simulateTamper'])->name('access-logs.simulate-tamper');
    Route::post('access-logs/restore-tamper', [AccessLogController::class, 'restoreTamper'])->name('access-logs.restore-tamper');
    Route::post('access-logs/check-unattended', [AccessLogController::class, 'checkUnattended'])->name('access-logs.check-unattended');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // Offline Records (events recorded by the lock box while it had no connection)
    Route::get('offline-records', [OfflineRecordController::class, 'index'])->name('offline.index');
    Route::get('offline-records/pending', [OfflineRecordController::class, 'pending'])->name('offline.pending');
    Route::post('offline-records/sync', [OfflineRecordController::class, 'sync'])->name('offline.sync');

    // Security alerts feed (header notification bell)
    Route::get('alerts/feed', [AlertController::class, 'feed'])->name('alerts.feed');
});

require __DIR__.'/auth.php';
