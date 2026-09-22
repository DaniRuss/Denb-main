@php
    $record = $getRecord();
    $status = $record->status;
    $am = app()->getLocale() === 'am';

    $steps = [
        'open'            => ['label' => $am ? 'ጅምር'              : 'Open',            'icon' => 'heroicon-o-clipboard-document-list'],
        'warning_issued'  => ['label' => $am ? 'ማስጠንቀቂያ'          : 'Warning',         'icon' => 'heroicon-o-exclamation-triangle'],
        'penalty_issued'  => ['label' => $am ? 'ቅጣት'              : 'Penalty',          'icon' => 'heroicon-o-document-text'],
        'payment_pending' => ['label' => $am ? 'ክፍያ በመጠበቅ'        : 'Pending',          'icon' => 'heroicon-o-clock'],
        'paid'            => ['label' => $am ? 'ተከፍሏል'            : 'Paid',             'icon' => 'heroicon-o-check-circle'],
        'court_filed'     => ['label' => $am ? 'ክስ ቀርቧል'          : 'Court',            'icon' => 'heroicon-o-building-library'],
        'closed'          => ['label' => $am ? 'ያለቀ'               : 'Closed',           'icon' => 'heroicon-o-lock-closed'],
    ];

    $isCourtPath = in_array($status, ['court_filed']) ||
        ($status === 'closed' && $record->warningLetters()->where('escalated_to_task_force', true)->exists());

    $path = $isCourtPath
        ? ['open', 'warning_issued', 'penalty_issued', 'payment_pending', 'court_filed', 'closed']
        : ['open', 'warning_issued', 'penalty_issued', 'payment_pending', 'paid', 'closed'];

    $currentIndex = array_search($status, $path);
    if ($currentIndex === false) $currentIndex = 0;

    // Colors: completed, current, future
    $completedColor = '#10b981'; // green
    $currentColors = [
        'open'            => '#6b7280',
        'warning_issued'  => '#f59e0b',
        'penalty_issued'  => '#3b82f6',
        'payment_pending' => '#f97316',
        'paid'            => '#10b981',
        'court_filed'     => '#ef4444',
        'closed'          => '#10b981',
    ];
    $futureColor = '#d1d5db';

    // Timeline events
    $events = collect();

    $events->push([
        'date' => $record->created_at,
        'label' => $am ? 'ደንብ መተላለፍ ተመዘገበ' : 'Violation Recorded',
        'by' => $record->reportedByUser?->name,
        'color' => '#6b7280',
    ]);

    if ($record->verified_by) {
        $events->push([
            'date' => $record->updated_at,
            'label' => $am ? 'በሽፍት መሪ ተረጋግጧል' : 'Verified by Shift Leader',
            'by' => $record->verifiedByUser?->name,
            'color' => '#10b981',
        ]);
    }

    foreach ($record->warningLetters()->with('issuedByUser')->orderBy('issued_date')->get() as $wl) {
        $type = $wl->warning_type === 'three_day'
            ? ($am ? 'የ3 ቀን ማስጠንቀቂያ ደብዳቤ ተሰጠ' : '3-Day Warning Letter Issued')
            : ($am ? 'የ24 ሰዓት ማስጠንቀቂያ ተሰጠ' : '24-Hour Warning Issued');
        $events->push([
            'date' => $wl->issued_date ?? $wl->created_at,
            'label' => $type,
            'by' => $wl->issuedByUser?->name,
            'color' => '#f59e0b',
        ]);
        if ($wl->escalated_to_task_force) {
            $events->push([
                'date' => $wl->escalation_date ?? $wl->updated_at,
                'label' => $am ? 'ወደ ታስክ ፎርስ ተላልፏል' : 'Escalated to Task Force',
                'by' => null,
                'color' => '#ef4444',
            ]);
        }
    }

    foreach ($record->penaltyReceipts()->with('issuedByUser')->orderBy('issued_date')->get() as $pr) {
        $events->push([
            'date' => $pr->issued_date ?? $pr->created_at,
            'label' => ($am ? 'የቅጣት ደረሰኝ #' : 'Penalty Receipt #') . $pr->receipt_number,
            'by' => $pr->issuedByUser?->name,
            'color' => '#3b82f6',
        ]);
        if ($pr->payment_status === 'paid') {
            $events->push([
                'date' => $pr->paid_date ?? $pr->updated_at,
                'label' => ($am ? 'ክፍያ ተፈጽሟል — ETB ' : 'Payment Received — ETB ') . number_format($pr->paid_amount, 2),
                'by' => null,
                'color' => '#10b981',
            ]);
        }
        if ($pr->payment_status === 'court_filed') {
            $events->push([
                'date' => $pr->court_filed_date ?? $pr->updated_at,
                'label' => $am ? 'ወደ ፍርድ ቤት ቀርቧል' : 'Filed to Court',
                'by' => null,
                'color' => '#ef4444',
            ]);
        }
    }

    foreach ($record->confiscatedAssets()->with('seizedByUser')->orderBy('seized_date')->get() as $ca) {
        $events->push([
            'date' => $ca->seized_date ?? $ca->created_at,
            'label' => ($am ? 'ንብረት ተያዘ — ' : 'Asset Seized — ') . $ca->description,
            'by' => $ca->seizedByUser?->name,
            'color' => '#3b82f6',
        ]);
    }

    $events = $events->sortBy('date')->values();
@endphp

{{-- ============================================================ --}}
{{--  HORIZONTAL PROGRESS LINE + TIMELINE (all inline styles)     --}}
{{-- ============================================================ --}}

<div style="padding: 8px 0;">

    {{-- ── Current Status Badge ── --}}
    @php $curColor = $currentColors[$status] ?? '#6b7280'; @endphp
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
        <span style="font-size:13px; font-weight:600; color:#6b7280;">
            {{ $am ? 'የአሁኑ ሁኔታ' : 'Current Status' }}:
        </span>
        <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 14px; border-radius:999px; font-size:13px; font-weight:700; color:#fff; background:{{ $curColor }}; letter-spacing:0.3px;">
            <x-filament::icon :icon="$steps[$status]['icon']" style="width:16px; height:16px;" />
            {{ $steps[$status]['label'] }}
        </span>
    </div>

    {{-- ── Horizontal Progress Stepper ── --}}
    <div style="display:flex; align-items:flex-start; width:100%; position:relative; padding:0 4px;">
        @foreach ($path as $index => $stepKey)
            @php
                $step = $steps[$stepKey];
                $isCompleted = $index < $currentIndex;
                $isCurrent   = $index === $currentIndex;
                $isFuture    = $index > $currentIndex;

                if ($isCompleted) {
                    $circBg     = $completedColor;
                    $circBorder = $completedColor;
                    $iconColor  = '#fff';
                    $labelColor = '#374151';
                    $labelWeight= '600';
                } elseif ($isCurrent) {
                    $circBg     = $currentColors[$stepKey] ?? '#6b7280';
                    $circBorder = $circBg;
                    $iconColor  = '#fff';
                    $labelColor = $circBg;
                    $labelWeight= '700';
                } else {
                    $circBg     = '#f3f4f6';
                    $circBorder = '#d1d5db';
                    $iconColor  = '#9ca3af';
                    $labelColor = '#9ca3af';
                    $labelWeight= '400';
                }
            @endphp

            @if ($index > 0)
                {{-- Connector line --}}
                @php $lineColor = $index <= $currentIndex ? $completedColor : '#e5e7eb'; @endphp
                <div style="flex:1; display:flex; align-items:center; padding-top:18px;">
                    <div style="width:100%; height:3px; border-radius:2px; background:{{ $lineColor }};"></div>
                </div>
            @endif

            {{-- Step --}}
            <div style="display:flex; flex-direction:column; align-items:center; position:relative; min-width:60px; max-width:90px;">
                {{-- Circle --}}
                <div style="width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center;
                    background:{{ $circBg }}; border:2.5px solid {{ $circBorder }};
                    {{ $isCurrent ? 'box-shadow:0 0 0 4px ' . $circBg . '33, 0 2px 8px rgba(0,0,0,0.15);' : '' }}
                    {{ $isCompleted ? 'box-shadow:0 1px 4px rgba(0,0,0,0.1);' : '' }}
                    position:relative; z-index:2;">
                    @if ($isCompleted)
                        {{-- Checkmark for completed --}}
                        <svg style="width:20px; height:20px; color:{{ $iconColor }};" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    @else
                        <x-filament::icon :icon="$step['icon']" style="width:18px; height:18px; color:{{ $iconColor }};" />
                    @endif
                </div>

                {{-- Label --}}
                <span style="margin-top:8px; font-size:11px; font-weight:{{ $labelWeight }}; color:{{ $labelColor }};
                    text-align:center; line-height:1.3; white-space:nowrap;">
                    {{ $step['label'] }}
                </span>

                {{-- Pulse for current --}}
                @if ($isCurrent)
                    <div style="position:absolute; top:-3px; left:50%; transform:translateX(-50%); width:10px; height:10px;">
                        <span style="position:absolute; display:inline-flex; width:100%; height:100%; border-radius:50%;
                            background:{{ $circBg }}; opacity:0.6; animation:escalation-ping 1.5s cubic-bezier(0,0,0.2,1) infinite;"></span>
                        <span style="position:relative; display:inline-flex; width:10px; height:10px; border-radius:50%; background:{{ $circBg }};"></span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ── Step descriptions (what happened at each stage) ── --}}
    <div style="display:flex; width:100%; padding:0 4px; margin-top:4px;">
        @foreach ($path as $index => $stepKey)
            @if ($index > 0)
                <div style="flex:1;"></div>
            @endif
            <div style="min-width:60px; max-width:90px; text-align:center;">
                @if ($index < $currentIndex)
                    <span style="font-size:10px; color:#10b981;">&#10003;</span>
                @elseif ($index === $currentIndex)
                    <span style="font-size:10px; color:{{ $currentColors[$stepKey] ?? '#6b7280' }};">&#9679; {{ $am ? 'አሁን' : 'Now' }}</span>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ── Timeline ── --}}
    @if ($events->count() > 0)
        <div style="margin-top:24px; border-top:1px solid #e5e7eb; padding-top:16px;">
            <h4 style="font-size:14px; font-weight:700; color:#374151; margin-bottom:14px;">
                {{ $am ? 'የእርምጃ ታሪክ' : 'Escalation Timeline' }}
            </h4>
            <div style="position:relative; padding-left:28px;">
                {{-- Vertical line --}}
                <div style="position:absolute; left:10px; top:6px; bottom:6px; width:2px; background:linear-gradient(to bottom, #10b981, #e5e7eb); border-radius:1px;"></div>

                @foreach ($events as $i => $event)
                    <div style="position:relative; margin-bottom:{{ $loop->last ? '0' : '18px' }}; display:flex; align-items:flex-start;">
                        {{-- Dot --}}
                        <div style="position:absolute; left:-22px; top:4px; width:12px; height:12px; border-radius:50%;
                            background:{{ $event['color'] }}; border:2.5px solid #fff; box-shadow:0 0 0 1px {{ $event['color'] }}44;"></div>
                        {{-- Content --}}
                        <div style="background:#f9fafb; border:1px solid #f3f4f6; border-radius:8px; padding:10px 14px; width:100%;">
                            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:4px;">
                                <span style="font-size:13px; font-weight:600; color:#1f2937;">
                                    {{ $event['label'] }}
                                </span>
                                <span style="font-size:11px; color:#9ca3af; white-space:nowrap;">
                                    {{ $event['date'] ? \Carbon\Carbon::parse($event['date'])->format('M d, Y — H:i') : '' }}
                                </span>
                            </div>
                            @if ($event['by'])
                                <div style="margin-top:3px; font-size:12px; color:#6b7280;">
                                    {{ $am ? 'በ' : 'By' }} {{ $event['by'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<style>
    @keyframes escalation-ping {
        75%, 100% { transform: scale(2.5); opacity: 0; }
    }
</style>
