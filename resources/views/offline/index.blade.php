@extends('layouts.app')

@section('title', 'Offline Records')

@section('content')
<div class="space-y-6" x-data="offlineRecords()">

    <!-- ═══════════════════════════════════
         PAGE HEADER
         ═══════════════════════════════════ -->
    <div class="mockup-card overflow-hidden relative">
        <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-gradient-to-br from-sky-200/50 to-violet-200/40 blur-2xl pointer-events-none"></div>
        <div class="relative flex items-start justify-between flex-wrap gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white flex items-center justify-center text-xl shadow-lg shadow-sky-500/25 flex-shrink-0">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div>
                    <h2 class="font-heading font-extrabold text-xl text-[var(--text-heading)]">Offline Records</h2>
                    <p class="text-xs text-[var(--text-muted)] font-medium mt-1 max-w-2xl leading-relaxed">
                        When the lock box loses its internet connection it keeps working and saves every scan, borrow and return on the device.
                        As soon as it reconnects, everything is <b>synced to the online database automatically</b> with the original times.
                        Use this page to review what happened while the box was offline.
                    </p>
                </div>
            </div>

            <!-- Connection status chips -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[11px] font-extrabold border"
                      :class="device.online ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full rounded-full opacity-75" :class="device.online ? 'bg-emerald-400 animate-ping' : 'bg-amber-400'"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" :class="device.online ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                    </span>
                    <span x-text="device.online ? 'Lock box: Online' : 'Lock box: Offline / not seen'"></span>
                </span>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[11px] font-extrabold border bg-violet-50 text-violet-700 border-violet-200">
                    <i class="fa-solid fa-bolt"></i> Auto-sync on
                </span>
            </div>
        </div>

        <!-- How it works -->
        <ol class="relative mt-5 grid grid-cols-1 sm:grid-cols-4 gap-3">
            @foreach ([
                ['fa-wifi', 'Lock box goes offline', 'Scans keep working using its saved copy of users & schedules.'],
                ['fa-hard-drive', 'Saved on the device', 'Borrows/returns are saved only after the IR sensor confirms them.'],
                ['fa-bolt', 'Synced automatically', 'On reconnect, records go straight to the online database.'],
                ['fa-magnifying-glass', 'You review', 'See everything that happened offline below.'],
            ] as $i => [$icon, $title, $text])
                <li class="p-3 rounded-2xl bg-[var(--app-bg)] border border-[var(--border-subtle)] flex gap-3">
                    <span class="w-7 h-7 rounded-lg bg-white border border-[var(--border-subtle)] text-[var(--purple-primary)] flex items-center justify-center text-xs flex-shrink-0">
                        <i class="fa-solid {{ $icon }}"></i>
                    </span>
                    <div>
                        <p class="text-[11px] font-extrabold text-[var(--text-heading)]">{{ $i + 1 }}. {{ $title }}</p>
                        <p class="text-[10px] text-[var(--text-muted)] font-medium leading-snug mt-0.5">{{ $text }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>

    <!-- ═══════════════════════════════════
         STATS
         ═══════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="mockup-stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon stat-badge-icon-emerald"><i class="fa-solid fa-circle-check"></i></div>
                <span class="text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Today</span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Synced Today</p>
            <h3 class="text-3xl font-heading font-extrabold text-emerald-600 mt-2">{{ $syncedToday }}</h3>
        </div>

        <div class="mockup-stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon"><i class="fa-solid fa-database"></i></div>
                <span class="text-[10px] font-extrabold text-[var(--purple-primary)] bg-[var(--purple-soft)] px-2 py-0.5 rounded-md uppercase tracking-wider">All Time</span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Offline Records Synced</p>
            <h3 class="text-3xl font-heading font-extrabold text-[var(--text-heading)] mt-2">{{ $syncedTotal }}</h3>
        </div>

        <div class="mockup-stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon stat-badge-missing-icon"><i class="fa-solid fa-user-shield"></i></div>
                <span class="text-[10px] font-extrabold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Security</span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Denied While Offline</p>
            <h3 class="text-3xl font-heading font-extrabold text-rose-600 mt-2">{{ $deniedTotal }}</h3>
        </div>

        <div class="mockup-stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="stat-badge-icon stat-badge-icon-amber"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <span class="text-[10px] font-extrabold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md uppercase tracking-wider">Review</span>
            </div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Skipped / Not Synced</p>
            <h3 class="text-3xl font-heading font-extrabold text-amber-600 mt-2"><span x-text="pendingCount + {{ $skippedTotal }}">{{ $pendingCount + $skippedTotal }}</span></h3>
        </div>
    </div>

    <!-- ═══════════════════════════════════
         DEVICE + RETRY (backup only)
         ═══════════════════════════════════ -->
    <div class="mockup-card">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-2 text-[11px]">
                <div>
                    <p class="text-[var(--text-muted)] font-semibold">Lock box last seen</p>
                    <p class="font-extrabold text-[var(--text-heading)]" x-text="device.last_seen ? `${device.last_seen_ago} (${device.last_seen})` : 'Never'"></p>
                </div>
                <div>
                    <p class="text-[var(--text-muted)] font-semibold">Device IP</p>
                    <p class="font-extrabold text-[var(--text-heading)] font-mono" x-text="device.ip || '—'"></p>
                </div>
                <div>
                    <p class="text-[var(--text-muted)] font-semibold">Last offline upload</p>
                    <p class="font-extrabold text-[var(--text-heading)]" x-text="device.last_upload || '—'"></p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" id="check-offline-data-btn" @click="load()" :disabled="loading"
                        title="Look for records that were uploaded but could not be synced"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-extrabold border border-[var(--border-subtle)] bg-white text-[var(--text-heading)] hover:border-[var(--purple-primary)] hover:text-[var(--purple-primary)] transition-all disabled:opacity-50">
                    <i class="fa-solid" :class="loading ? 'fa-spinner animate-spin' : 'fa-magnifying-glass'"></i>
                    <span x-text="loading ? 'Checking...' : 'Check Unsynced Records'"></span>
                </button>
                <button type="button" id="sync-offline-data-btn" @click="sync()" x-show="pendingCount > 0" x-cloak :disabled="syncing"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-extrabold text-white bg-gradient-to-r from-amber-500 to-orange-600 shadow-md shadow-amber-500/25 transition-all disabled:opacity-50">
                    <i class="fa-solid" :class="syncing ? 'fa-spinner animate-spin' : 'fa-rotate'"></i>
                    <span x-text="syncing ? 'Syncing...' : `Retry Sync (${pendingCount})`"></span>
                </button>
            </div>
        </div>

        <!-- Unsynced records (normally empty) -->
        <div x-show="loaded" x-cloak class="mt-4">
            <div x-show="records.length === 0" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-700 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> Everything is synced. No records are waiting.
            </div>
            <div x-show="records.length > 0" class="overflow-x-auto rounded-2xl border border-amber-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-amber-50 text-[10px] uppercase tracking-wider text-amber-800">
                        <tr>
                            <th class="p-3 font-extrabold">Recorded At</th>
                            <th class="p-3 font-extrabold">User</th>
                            <th class="p-3 font-extrabold">Slot</th>
                            <th class="p-3 font-extrabold">Action</th>
                            <th class="p-3 font-extrabold">Details</th>
                            <th class="p-3 font-extrabold text-right">Card</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100">
                        <template x-for="r in records" :key="r.id">
                            <tr class="bg-white">
                                <td class="p-3 whitespace-nowrap font-semibold" x-text="r.occurred_at"></td>
                                <td class="p-3 font-bold" x-text="r.user"></td>
                                <td class="p-3 font-mono" x-text="r.slot ? '#' + r.slot : (r.slot_number ? '#' + r.slot_number : '—')"></td>
                                <td class="p-3 capitalize" x-text="(r.action || '—').replace('_', ' ')"></td>
                                <td class="p-3 text-[var(--text-heading)] min-w-[240px] max-w-sm leading-relaxed break-words font-medium text-[11px]" x-text="r.reason || '—'"></td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <button type="button" @click="openCard(r)"
                                            class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-amber-100 text-amber-800 hover:bg-amber-600 hover:text-white transition-all shadow-sm inline-flex items-center gap-1">
                                        <i class="fa-solid fa-id-card text-[9px]"></i> View Card
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════
         OFFLINE ACTIVITY HISTORY
         ═══════════════════════════════════ -->
    <div class="mockup-card">
        <div class="flex items-center justify-between flex-wrap gap-3 pb-4 border-b border-[var(--border-subtle)]">
            <div>
                <h3 class="mockup-card-title text-base flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-[var(--purple-primary)]"></i> What Happened While Offline
                </h3>
                <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">Every record the lock box saved offline, newest first (times are Philippine Standard Time)</p>
            </div>
            <div class="flex items-center gap-1 p-1 rounded-xl bg-[var(--app-bg)] border border-[var(--border-subtle)] text-[11px] font-extrabold">
                @foreach ([null => 'All', 'transactions' => 'Borrows & Returns', 'denied' => 'Denied'] as $key => $label)
                    <a href="{{ route('offline.index', array_filter(['show' => $key])) }}"
                       class="px-3 py-1.5 rounded-lg transition-all {{ $filter === $key ? 'bg-white shadow-sm text-[var(--purple-primary)]' : 'text-[var(--text-muted)] hover:text-[var(--text-heading)]' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if($history->isEmpty())
            <div class="py-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-cloud"></i>
                </div>
                <p class="text-sm font-extrabold text-[var(--text-heading)] mt-3">No offline activity yet</p>
                <p class="text-xs text-[var(--text-muted)] mt-1">Records will appear here after the lock box has been offline and reconnects.</p>
            </div>
        @else
            <div class="mt-4 overflow-x-auto rounded-2xl border border-[var(--border-subtle)]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[var(--app-bg)] text-[10px] uppercase tracking-wider text-[var(--text-muted)]">
                        <tr>
                            <th class="p-3 font-extrabold">Recorded At</th>
                            <th class="p-3 font-extrabold">Type</th>
                            <th class="p-3 font-extrabold">User</th>
                            <th class="p-3 font-extrabold">Slot</th>
                            <th class="p-3 font-extrabold">Action</th>
                            <th class="p-3 font-extrabold">Result</th>
                            <th class="p-3 font-extrabold">Details</th>
                            <th class="p-3 font-extrabold">Synced</th>
                            <th class="p-3 font-extrabold text-right">Card</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border-subtle)]">
                        @php $lastDate = null; @endphp
                        @foreach($history as $row)
                            @php $rowDate = $row->occurred_at?->format('l, F j, Y'); @endphp
                            @if($rowDate !== $lastDate)
                                <tr class="bg-[var(--purple-soft)]/60">
                                    <td colspan="9" class="px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-[var(--purple-primary)]">
                                        <i class="fa-regular fa-calendar"></i> {{ $rowDate }}
                                    </td>
                                </tr>
                                @php $lastDate = $rowDate; @endphp
                            @endif
                            <tr class="bg-white hover:bg-[var(--app-bg)] transition-colors">
                                <td class="p-3 whitespace-nowrap font-bold text-[var(--text-heading)]">{{ $row->occurred_at?->format('h:i:s A') }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider {{ $row->type === 'transaction' ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $row->type === 'transaction' ? 'Key Transaction' : 'Scan Log' }}
                                    </span>
                                </td>
                                <td class="p-3 font-bold">
                                    @if($row->user)
                                        {{ $row->user->name }}
                                    @else
                                        <span class="inline-flex items-center gap-1 text-slate-700"><i class="fa-solid fa-user-secret"></i> Unknown QR</span>
                                    @endif
                                </td>
                                <td class="p-3 font-mono">{{ $row->slot_number ? '#' . $row->slot_number : '—' }}</td>
                                <td class="p-3 capitalize font-semibold">{{ str_replace('_', ' ', $row->action ?? '—') }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase {{ $row->result === 'denied' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $row->result ?? 'granted' }}
                                    </span>
                                </td>
                                <td class="p-3 text-[var(--text-heading)] min-w-[260px] max-w-md">
                                    <span class="block leading-relaxed font-medium break-words text-[11px]">{{ $row->reason ?: '—' }}</span>
                                    @if($row->status === 'skipped')
                                        <span class="block text-[10px] font-bold text-amber-700 mt-1"><i class="fa-solid fa-triangle-exclamation"></i> Skipped: {{ $row->sync_note }}</span>
                                    @endif
                                </td>
                                <td class="p-3 whitespace-nowrap text-[var(--text-muted)]">
                                    {{ $row->synced_at?->format('M d, h:i A') }}
                                    <span class="block text-[10px] font-bold {{ $row->syncedBy ? 'text-[var(--purple-primary)]' : 'text-emerald-600' }}">
                                        {{ $row->syncedBy ? 'by ' . $row->syncedBy->name : 'Automatic' }}
                                    </span>
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <button type="button"
                                            @click="openCard({{ json_encode([
                                                'uid' => $row->uid,
                                                'occurred_at' => $row->occurred_at?->format('M d, Y · h:i:s A'),
                                                'type' => $row->type === 'transaction' ? 'Key Transaction' : 'Scan Log',
                                                'user' => $row->user ? $row->user->name : 'Unknown QR Code',
                                                'slot' => $row->slot_number ? '#' . $row->slot_number : '—',
                                                'action' => ucfirst(str_replace('_', ' ', $row->action ?? '—')),
                                                'result' => $row->result ?? 'granted',
                                                'reason' => $row->reason ?: '—',
                                                'synced_at' => $row->synced_at?->format('M d, Y · h:i:s A') ?? 'Pending Sync',
                                                'synced_by' => $row->syncedBy ? 'Admin (' . $row->syncedBy->name . ')' : 'Automatic Auto-Sync',
                                                'status' => $row->status,
                                                'sync_note' => $row->sync_note,
                                                'qr_token' => $row->payload['qr_token'] ?? null,
                                                'ip_address' => $row->payload['ip_address'] ?? null,
                                            ]) }})"
                                            class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-[var(--purple-soft)] text-[var(--purple-primary)] hover:bg-[var(--purple-primary)] hover:text-white transition-all shadow-sm inline-flex items-center gap-1">
                                        <i class="fa-solid fa-id-card text-[9px]"></i> View Card
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $history->links() }}</div>
        @endif
    </div>

    <!-- ═══════════════════════════════════
         OFFLINE RECORD DETAILS MODAL CARD
         ═══════════════════════════════════ -->
    <div x-show="cardModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="cardModalOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">

        <div @click.away="cardModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden text-left z-50 flex flex-col max-h-[90vh]">

            <!-- Modal Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-purple-50 via-slate-50 to-indigo-50 border-b border-[var(--border-subtle)] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[var(--purple-soft)] text-[var(--purple-primary)] text-lg flex items-center justify-center font-extrabold shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-extrabold text-base text-[var(--text-heading)]">Offline Record Details</h3>
                        <p class="text-[11px] text-[var(--text-muted)] font-mono" x-text="activeRecord?.uid || '—'"></p>
                    </div>
                </div>
                <button type="button" @click="cardModalOpen = false"
                        class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-all flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Content (Scrollable if tall) -->
            <div class="p-6 space-y-4 overflow-y-auto">
                <!-- Status Badges Row -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-violet-100 text-violet-700"
                          x-text="activeRecord?.type"></span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider"
                          :class="activeRecord?.result === 'denied' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
                          x-text="activeRecord?.result"></span>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider"
                          :class="activeRecord?.status === 'synced' ? 'bg-sky-100 text-sky-700' : (activeRecord?.status === 'skipped' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700')"
                          x-text="'Status: ' + (activeRecord?.status || 'pending')"></span>
                </div>

                <!-- Full Reason / Details Highlight Box -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-[var(--text-muted)] mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-[var(--purple-primary)]"></i> Full Details & Reason
                    </p>
                    <p class="text-sm font-bold text-[var(--text-heading)] leading-relaxed select-text" x-text="activeRecord?.reason || '—'"></p>
                    <div x-show="activeRecord?.sync_note" class="mt-2 pt-2 border-t border-slate-200 text-xs font-semibold text-amber-700">
                        <i class="fa-solid fa-triangle-exclamation"></i> Sync note: <span x-text="activeRecord?.sync_note"></span>
                    </div>
                </div>

                <!-- 2-Column Info Grid -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-[var(--app-bg)] border border-[var(--border-subtle)]">
                        <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Recorded At (Offline)</p>
                        <p class="font-extrabold text-[var(--text-heading)] mt-0.5" x-text="activeRecord?.occurred_at || '—'"></p>
                    </div>

                    <div class="p-3 rounded-xl bg-[var(--app-bg)] border border-[var(--border-subtle)]">
                        <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">User / Holder</p>
                        <p class="font-extrabold text-[var(--text-heading)] mt-0.5" x-text="activeRecord?.user || '—'"></p>
                    </div>

                    <div class="p-3 rounded-xl bg-[var(--app-bg)] border border-[var(--border-subtle)]">
                        <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Key Slot Number</p>
                        <p class="font-extrabold text-[var(--text-heading)] font-mono mt-0.5" x-text="activeRecord?.slot || '—'"></p>
                    </div>

                    <div class="p-3 rounded-xl bg-[var(--app-bg)] border border-[var(--border-subtle)]">
                        <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Action</p>
                        <p class="font-extrabold text-[var(--text-heading)] mt-0.5" x-text="activeRecord?.action || '—'"></p>
                    </div>

                    <div class="p-3 rounded-xl bg-[var(--app-bg)] border border-[var(--border-subtle)]">
                        <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Sync Method</p>
                        <p class="font-extrabold text-[var(--text-heading)] mt-0.5" x-text="activeRecord?.synced_by || '—'"></p>
                    </div>

                    <div class="p-3 rounded-xl bg-[var(--app-bg)] border border-[var(--border-subtle)]">
                        <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Synced To Online DB</p>
                        <p class="font-extrabold text-[var(--text-heading)] mt-0.5" x-text="activeRecord?.synced_at || '—'"></p>
                    </div>
                </div>

                <!-- Optional QR Token & IP row if present -->
                <div x-show="activeRecord?.qr_token || activeRecord?.ip_address" class="p-3 rounded-xl bg-violet-50/60 border border-violet-100 flex items-center justify-between text-xs flex-wrap gap-2">
                    <div x-show="activeRecord?.qr_token">
                        <span class="text-[10px] font-extrabold text-violet-800 uppercase tracking-wider">Scanned QR:</span>
                        <code class="font-mono font-bold text-violet-900 bg-white px-2 py-0.5 rounded border border-violet-200 ml-1 select-all" x-text="activeRecord?.qr_token"></code>
                    </div>
                    <div x-show="activeRecord?.ip_address">
                        <span class="text-[10px] font-extrabold text-violet-800 uppercase tracking-wider">Device IP:</span>
                        <span class="font-mono font-bold text-slate-700 ml-1" x-text="activeRecord?.ip_address"></span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-[var(--app-bg)] border-t border-[var(--border-subtle)] flex items-center justify-end">
                <button type="button" @click="cardModalOpen = false"
                        class="px-5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition-colors shadow-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function offlineRecords() {
        return {
            records: [],
            loaded: false,
            loading: false,
            syncing: false,
            pendingCount: {{ $pendingCount }},
            device: @json($device),
            cardModalOpen: false,
            activeRecord: null,

            openCard(rec) {
                this.activeRecord = rec;
                this.cardModalOpen = true;
            },

            async load() {
                this.loading = true;
                try {
                    const res = await fetch(@json(route('offline.pending')), { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();
                    this.records = data.records;
                    this.pendingCount = data.count;
                    this.device = data.device;
                    this.loaded = true;
                } catch (e) {
                    showToast('Could not check for unsynced records. Check your connection and try again.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async sync() {
                this.syncing = true;
                try {
                    const res = await fetch(@json(route('offline.sync')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({}),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) throw new Error(data.message || 'Sync failed');
                    showToast(data.message, 'success', 6000);
                    setTimeout(() => window.location.reload(), 1500);
                } catch (e) {
                    showToast(e.message || 'Sync failed. Please try again.', 'error');
                    this.syncing = false;
                }
            },
        };
    }
</script>
@endsection
