<?php

namespace App\Models;

use App\Events\SecurityAlertRaised;
use App\Http\Controllers\AlertController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AccessLog extends Model
{
    use HasFactory;

    /**
     * Set to true to stop live alerts (e.g. while importing old offline records).
     */
    public static bool $muteAlerts = false;

    protected $fillable = [
        'user_id', 'qr_token', 'action',
        'result', 'reason', 'ip_address',
        'previous_hash', 'record_hash',
    ];

    protected static function booted(): void
    {
        // Automatically calculate cryptographic SHA-256 hash chain before record creation
        static::creating(function (AccessLog $log) {
            \App\Services\AuditChainService::seal($log);
        });

        // Every denied attempt (except physical removal without QR) is pushed to admin screens in real time via Pusher.
        static::created(function (AccessLog $log) {
            if ($log->result !== 'denied' || $log->action === 'missing' || static::$muteAlerts) {
                return;
            }

            try {
                $log->loadMissing('user:id,name');
                SecurityAlertRaised::dispatch(
                    AlertController::present($log),
                    static::where('result', 'denied')->where('action', '!=', 'missing')->whereDate('created_at', today())->count()
                );
            } catch (\Throwable $e) {
                // Pusher unreachable: the bell's fallback polling will still pick it up.
                Log::warning('[BROADCAST] SecurityAlertRaised skipped: ' . $e->getMessage());
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
