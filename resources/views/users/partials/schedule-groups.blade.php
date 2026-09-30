{{-- resources/views/users/partials/schedule-groups.blade.php
     Usage: @include('users.partials.schedule-groups', ['user' => $user])
     Groups schedules that share the same key + time window, so
     Mon/Wed/Fri 8:00–9:00 AM becomes ONE row instead of three. --}}

@php
    $order = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

    // "monday,tuesday,wednesday,thursday,friday" -> "Mon–Fri"; "monday,wednesday" -> "Mon, Wed"
    $formatDays = function (array $days) use ($order) {
        $idx = collect($days)
            ->map(fn ($d) => array_search(strtolower($d), $order))
            ->filter(fn ($i) => $i !== false)
            ->unique()->sort()->values()->all();

        if (count($idx) === 7) return 'Every day';

        $short = fn ($i) => ucfirst(substr($order[$i], 0, 3));
        $parts = [];
        for ($i = 0; $i < count($idx); $i = $j + 1) {
            $j = $i;
            while ($j + 1 < count($idx) && $idx[$j + 1] === $idx[$j] + 1) $j++;
            if ($j - $i >= 2) {
                $parts[] = $short($idx[$i]) . '–' . $short($idx[$j]);   // run of 3+ days
            } else {
                for ($k = $i; $k <= $j; $k++) $parts[] = $short($idx[$k]);
            }
        }
        return implode(', ', $parts);
    };

    $groups = $user->schedules
        ->groupBy(fn ($s) => $s->key_id . '|' . $s->start_time . '|' . $s->end_time . '|' . (int) $s->is_active)
        ->sortBy(fn ($g) => $g->first()->start_time);
@endphp

<div class="space-y-1.5">
    @foreach($groups as $group)
        @php $s = $group->first(); $k = $s->key; @endphp
        <div class="flex items-center justify-between gap-2 p-2 rounded-xl bg-slate-50 border border-slate-300 text-xs">
            <div class="space-y-0.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-800 border border-purple-200 font-mono font-extrabold text-[10px]">
                        Slot #{{ $k->slot_number ?? '?' }}
                    </span>
                    <span class="font-extrabold text-slate-900">{{ $k->key_name ?? 'Key Slot' }}</span>
                    @if($k?->room_name)
                        <span class="text-slate-600 text-[11px] font-medium">({{ $k->room_name }})</span>
                    @endif
                </div>

                <div class="text-[11px] text-slate-700 flex items-center gap-1.5 font-medium flex-wrap">
                    <span class="font-bold text-slate-900">{{ $formatDays($group->pluck('day_of_week')->all()) }}</span>
                    <span>&bull;</span>
                    <span class="font-mono text-emerald-800 font-bold">{{ \Carbon\Carbon::parse($s->start_time)->format('h:i A') }}</span>
                    <span>-</span>
                    <span class="font-mono text-rose-800 font-bold">{{ \Carbon\Carbon::parse($s->end_time)->format('h:i A') }}</span>
                </div>
            </div>

            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold border flex-shrink-0 {{ $s->is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-600 border-slate-300' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $s->is_active ? 'bg-emerald-600' : 'bg-slate-400' }}"></span>
                {{ $s->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    @endforeach
</div>