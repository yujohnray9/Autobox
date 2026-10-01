{{-- resources/views/users/partials/schedule-groups.blade.php
     Usage: @include('users.partials.schedule-groups', ['user' => $user])
     Combines multiple time slots for the same room and day(s) into compact rows in military time (e.g., 0800–1000, 1200–1300) --}}

@php
    $order = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

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
                $parts[] = $short($idx[$i]) . '–' . $short($idx[$j]);
            } else {
                for ($k = $i; $k <= $j; $k++) $parts[] = $short($idx[$k]);
            }
        }
        return implode(', ', $parts);
    };

    $formatMilitary = function ($start, $end) {
        return \Carbon\Carbon::parse($start)->format('Hi') . '–' . \Carbon\Carbon::parse($end)->format('Hi');
    };

    // Group schedules by Room (key_id)
    $schedulesByRoom = $user->schedules
        ->sortBy('start_time')
        ->groupBy('key_id');
@endphp

<div class="space-y-2">
    @foreach($schedulesByRoom as $keyId => $roomSchedules)
        @php
            $first = $roomSchedules->first();
            $k = $first->key;
            $allActive = $roomSchedules->every(fn ($s) => $s->is_active);

            // Group by day of week
            $dayGroups = $roomSchedules->groupBy(fn ($s) => strtolower($s->day_of_week));

            // Group identical time sets across days
            $timeToDays = [];
            foreach ($order as $dayName) {
                if (!isset($dayGroups[$dayName])) continue;
                $dayItems = $dayGroups[$dayName]->sortBy('start_time');
                $timeString = $dayItems->map(fn ($s) => $formatMilitary($s->start_time, $s->end_time))->unique()->implode(', ');
                $timeToDays[$timeString][] = $dayName;
            }
        @endphp

        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-left">
            <div class="flex items-center justify-between gap-2 border-b border-slate-200/80 pb-1.5 mb-1.5">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-800 border border-purple-200 font-mono font-extrabold text-[10px]">
                        Slot #{{ $k->slot_number ?? '?' }}
                    </span>
                    <span class="font-extrabold text-slate-900">{{ $k->key_name ?? 'Key Slot' }}</span>
                    @if($k?->room_name && $k->room_name !== $k->key_name)
                        <span class="text-slate-600 text-[11px] font-medium">({{ $k->room_name }})</span>
                    @endif
                </div>

                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold border flex-shrink-0 {{ $allActive ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-amber-50 text-amber-800 border-amber-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $allActive ? 'bg-emerald-600' : 'bg-amber-500' }}"></span>
                    {{ $allActive ? 'Active' : 'Partially Inactive' }}
                </span>
            </div>

            <!-- Combined Day & Military Time Slots -->
            <div class="space-y-1">
                @foreach($timeToDays as $timeRanges => $daysList)
                    <div class="text-[11px] text-slate-700 flex items-center gap-2 font-medium flex-wrap">
                        <span class="font-bold text-slate-900 bg-white px-1.5 py-0.5 rounded border border-slate-200 text-[10px]">
                            {{ $formatDays($daysList) }}
                        </span>
                        <span class="font-mono text-purple-900 font-bold tracking-tight">
                            {{ $timeRanges }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>