<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use Illuminate\Http\Request;

class AccessLogController extends Controller
{
    public function index()
    {
        $logs = AccessLog::with('user')->latest()->paginate(20);
        return view('access_logs.index', compact('logs'));
    }
}
