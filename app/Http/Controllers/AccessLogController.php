<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Services\AuditChainService;
use Illuminate\Http\Request;

class AccessLogController extends Controller
{
    public function index(AuditChainService $auditChain)
    {
        $logs = AccessLog::with('user')->latest('id')->paginate(20);
        $chainStatus = $auditChain->verifyChain();

        return view('access_logs.index', compact('logs', 'chainStatus'));
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
