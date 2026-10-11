<?php

namespace App\Http\Controllers;

use App\Models\OfflineRecord;
use App\Services\OfflineSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OfflineRecordController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('show'); // null | transactions | denied

        $history = OfflineRecord::with(['user:id,name', 'syncedBy:id,name'])
            ->where('status', '!=', 'pending')
            ->when($filter === 'transactions', fn ($q) => $q->where('type', 'transaction'))
            ->when($filter === 'denied', fn ($q) => $q->where('result', 'denied'))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('offline.index', [
            'history'      => $history,
            'filter'       => $filter,
            'pendingCount' => OfflineRecord::pending()->count(),
            'syncedTotal'  => OfflineRecord::where('status', 'synced')->count(),
            'syncedToday'  => OfflineRecord::where('status', 'synced')->whereDate('occurred_at', today())->count(),
            'deniedTotal'  => OfflineRecord::where('status', 'synced')->where('result', 'denied')->count(),
            'skippedTotal' => OfflineRecord::where('status', 'skipped')->count(),
            'device'       => $this->deviceStatus(),
        ]);
    }

    /**
     * "Check Offline Data" button — returns the records the device uploaded that are waiting for sync.
     */
    public function pending()
    {
        $records = OfflineRecord::with('user:id,name')
            ->pending()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get()
            ->map(fn (OfflineRecord $r) => [
                'id'          => $r->id,
                'uid'         => $r->uid,
                'type'        => $r->type === 'transaction' ? 'Key Transaction' : 'Scan Log',
                'user'        => $r->user?->name ?? ($r->type === 'access_log' ? 'Unknown QR Code' : 'Unknown User'),
                'slot'        => $r->slot_number ? '#' . $r->slot_number : '—',
                'action'      => ucfirst(str_replace('_', ' ', $r->action ?? '—')),
                'result'      => $r->result ?? 'granted',
                'reason'      => $r->reason ?: '—',
                'occurred_at' => $r->occurred_at?->format('M d, Y · h:i:s A'),
                'synced_at'   => 'Pending Sync',
                'synced_by'   => 'Awaiting upload/sync',
                'status'      => 'pending',
                'qr_token'    => $r->payload['qr_token'] ?? null,
                'ip_address'  => $r->payload['ip_address'] ?? null,
            ]);

        return response()->json([
            'records' => $records,
            'count'   => $records->count(),
            'device'  => $this->deviceStatus(),
        ]);
    }

    /**
     * "Sync to Online Database" button — writes pending records into the live tables.
     */
    public function sync(Request $request, OfflineSyncService $sync)
    {
        $validated = $request->validate([
            'ids'   => 'nullable|array',
            'ids.*' => 'integer',
        ]);

        $result = $sync->applyPending($request->user(), $validated['ids'] ?? null);

        $message = "Synced {$result['synced']} offline record(s) to the online database"
            . ($result['skipped'] ? " ({$result['skipped']} skipped — see history for details)." : '.');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message] + $result);
        }

        return redirect()->route('offline.index')->with('success', $message);
    }

    protected function deviceStatus(): array
    {
        $seen = Cache::get('autobox_device_last_seen');
        $lastSeen = $seen ? Carbon::parse($seen['at']) : null;

        return [
            // Pi polls /api/keys every 30s, so > 2 minutes of silence means it is offline
            'online'       => $lastSeen && $lastSeen->gt(now()->subMinutes(2)),
            'last_seen'    => $lastSeen?->format('M d, Y h:i:s A'),
            'last_seen_ago'=> $lastSeen?->diffForHumans(),
            'ip'           => $seen['ip'] ?? null,
            'last_upload'  => ($u = Cache::get('autobox_offline_last_upload')) ? Carbon::parse($u)->format('M d, Y h:i:s A') : null,
        ];
    }
}
