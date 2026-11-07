<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\OfflineRecord;
use Illuminate\Http\Request;

/**
 * Lightweight polling feed for the admin notification bell.
 * Works even when the Pusher/WebSocket connection is unavailable.
 */
class AlertController extends Controller
{
    public function feed(Request $request)
    {
        $afterId = (int) $request->query('after_id', 0);

        $query = AccessLog::with('user:id,name')
            ->where('result', 'denied')
            ->where('action', '!=', 'missing')
            ->latest('id');

        $alerts = ($afterId > 0 ? $query->where('id', '>', $afterId) : $query)
            ->take(15)
            ->get()
            ->map(fn (AccessLog $log) => self::present($log));

        return response()->json([
            'alerts'          => $alerts,
            'latest_id'       => (int) (AccessLog::where('result', 'denied')->where('action', '!=', 'missing')->max('id') ?? 0),
            'today_count'     => AccessLog::where('result', 'denied')->where('action', '!=', 'missing')->whereDate('created_at', today())->count(),
            'pending_offline' => OfflineRecord::pending()->count(),
            'server_time'     => now()->toIso8601String(),
        ]);
    }

    /**
     * Convert a denied access log into a human-friendly alert.
     */
    public static function present(AccessLog $log): array
    {
        return [
            'id'         => $log->id,
            'who'        => $log->user?->name ?? ($log->action === 'missing' ? 'IR Sensor (No QR Scan)' : 'Unknown QR Code'),
            'is_unknown' => $log->user_id === null,
            'category'   => self::category($log),
            'reason'     => $log->reason,
            'action'     => $log->action,
            'time'       => $log->created_at?->format('M d, h:i:s A'),
            'ago'        => $log->created_at?->diffForHumans(),
        ];
    }

    public static function category(AccessLog $log): string
    {
        $reason = strtolower((string) $log->reason);

        return match (true) {
            $log->action === 'security_alert'                     => 'Repeated Scans',
            $log->action === 'missing'                            => 'Key Removed Without Scan',
            $log->action === 'absent' || str_contains($reason, 'absent') || str_contains($reason, 'not attend') => 'Absent / Class Unattended',
            $log->user_id === null                                => 'Unregistered QR',
            str_contains($reason, 'schedule')                     => 'Outside Schedule',
            str_contains($reason, 'not return')                   => 'Key Not Yet Returned',
            str_contains($reason, 'ir sensor'),
            str_contains($reason, 'not removed')                  => 'Incomplete Borrow/Return',
            default                                               => 'Access Denied',
        };
    }
}
