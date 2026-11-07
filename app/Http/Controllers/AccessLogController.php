<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Services\AuditChainService;
use App\Console\Commands\CheckUnattendedSchedules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccessLogController extends Controller
{
    public function index(AuditChainService $auditChain)
    {
        // Auto-scan for unattended class schedules
        try {
            CheckUnattendedSchedules::scanUnattendedSchedules();
        } catch (\Throwable $e) {
            Log::warning("[ACCESS LOGS] Auto-scan unattended schedules error: " . $e->getMessage());
        }

        $logs = AccessLog::with('user')->latest('id')->paginate(20);
        $chainStatus = $auditChain->verifyChain();

        return view('access_logs.index', compact('logs', 'chainStatus'));
    }

    /**
     * Test / Admin Action: Scan class schedules and record audit logs for any unattended keys.
     */
    public function checkUnattended(Request $request)
    {
        $force = $request->boolean('force', true);
        $day   = $request->filled('day') ? $request->input('day') : null;

        $count = CheckUnattendedSchedules::scanUnattendedSchedules($force, $day);

        $dayLabel = $day ? ucfirst($day) : 'today';
        $msg = $count > 0
            ? "Scan complete: Recorded {$count} absent / unattended class schedule audit log(s) for {$dayLabel}."
            : "Scan complete: All scheduled keys for {$dayLabel} are accounted for. No new absent schedules detected.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $count,
                'message' => $msg,
            ]);
        }

        return redirect()->route('access-logs.index')
            ->with($count > 0 ? 'warning' : 'success', $msg);
    }

    /**
     * AJAX endpoint: Live verification of cryptographic SHA-256 hash chain.
     */
    public function checkIntegrity(AuditChainService $auditChain)
    {
        $result = $auditChain->verifyChain();
        return response()->json($result);
    }

    /**
     * Test Tool: Simulate a database tamper on an existing record.
     */
    public function simulateTamper(Request $request, AuditChainService $auditChain)
    {
        $result = $auditChain->simulateTamper();

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('access-logs.index')
            ->with('warning', $result['message']);
    }

    /**
     * Test Tool: Restore the database and reseal the cryptographic chain.
     */
    public function restoreTamper(Request $request, AuditChainService $auditChain)
    {
        $result = $auditChain->restoreTamper();

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('access-logs.index')
            ->with('success', $result['message']);
    }
}
