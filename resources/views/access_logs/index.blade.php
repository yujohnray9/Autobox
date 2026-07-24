@extends('layouts.app')

@section('title', 'QR Audit Logs')

@section('content')
<div class="space-y-6">

    <div>
        <h2 class="text-xl font-heading font-bold text-slate-900">QR Code Scan Audit Trail</h2>
        <p class="text-xs text-slate-500 mt-0.5">Real-time log of all QR scanner authentication attempts from hardware scanners</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">Timestamp</th>
                        <th class="p-4">User</th>
                        <th class="p-4">Scanned QR Token</th>
                        <th class="p-4">Action</th>
                        <th class="p-4">Result</th>
                        <th class="p-4">Reason / Details</th>
                        <th class="p-4">Scanner IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="p-4 font-mono text-slate-600">
                                {{ $log->created_at->format('M d, Y h:i:s A') }}
                            </td>
                            <td class="p-4">
                                <span class="font-bold text-slate-900 block text-sm">{{ $log->user->name ?? 'Unregistered' }}</span>
                                <span class="text-slate-400 text-xs">{{ $log->user->employee_id ?? 'No Account' }}</span>
                            </td>
                            <td class="p-4 font-mono text-slate-500 max-w-[140px] truncate">
                                {{ $log->qr_token }}
                            </td>
                            <td class="p-4 font-semibold uppercase text-slate-700">
                                {{ $log->action }}
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase
                                    @if($log->result === 'granted') bg-emerald-100 text-emerald-800 @else bg-rose-100 text-rose-800 @endif">
                                    {{ $log->result }}
                                </span>
                            </td>
                            <td class="p-4 text-slate-600">
                                {{ $log->reason }}
                            </td>
                            <td class="p-4 font-mono text-slate-400">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">No scan logs recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
