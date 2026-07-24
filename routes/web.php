<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KeyController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AccessLogController;
use App\Http\Controllers\ReportController;
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

    // Access Logs
    Route::get('access-logs', [AccessLogController::class, 'index'])->name('access-logs.index');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
});

require __DIR__.'/auth.php';
