@php
    $data = $this->getViewData();
@endphp

@if($data['show'] ?? false)
    <x-filament-widgets::widget>
        <x-filament::section>
            <x-slot name="heading">
                My attendance — {{ $data['assignment'] ? $data['assignment']->shift?->name . ' · ' . $data['assignment']->assigned_date?->format('M j, Y') : 'Today' }}
            </x-slot>

            @if(! $data['assignment'])
                <p class="text-gray-600 dark:text-gray-400">
                    You have no shift assigned for today. Check-in and check-out are available only on days you have a scheduled shift.
                </p>
            @elseif(! $data['withinShift'])
                <p class="text-amber-700 dark:text-amber-400">
                    You can check in and check out only during your shift ({{ \Carbon\Carbon::parse($data['assignment']->assigned_date->format('Y-m-d') . ' ' . $data['assignment']->shift->start_time)->format('g:i A') }}
                    – {{ \Carbon\Carbon::parse($data['assignment']->assigned_date->format('Y-m-d') . ' ' . $data['assignment']->shift->end_time)->format('g:i A') }}).
                </p>
            @elseif($data['canCheckIn'])
                <form wire:submit="checkIn" class="space-y-4">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            wire:model="checkInLocation"
                            placeholder="Check-in location (optional)"
                            class="w-full"
                        />
                    </x-filament::input.wrapper>
                    <x-filament::button type="submit" color="success" icon="heroicon-o-check-circle">
                        Check in
                    </x-filament::button>
                </form>
            @elseif($data['canCheckOut'])
                <form wire:submit="checkOut" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Checked in at {{ $data['attendance']->check_in?->format('g:i A') }}
                        @if($data['attendance']->check_in_location)
                            · {{ $data['attendance']->check_in_location }}
                        @endif
                    </p>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            wire:model="checkOutLocation"
                            placeholder="Check-out location (optional)"
                            class="w-full"
                        />
                    </x-filament::input.wrapper>
                    <x-filament::button type="submit" color="primary" icon="heroicon-o-arrow-right-on-rectangle">
                        Check out & go to report
                    </x-filament::button>
                </form>
            @elseif($data['checkedOut'])
                <p class="text-gray-600 dark:text-gray-400">
                    Check-out recorded at {{ $data['attendance']->check_out?->format('g:i A') }}
                    @if($data['attendance']->check_out_location)
                        · {{ $data['attendance']->check_out_location }}
                    @endif
                </p>
                <a href="{{ \App\Filament\Resources\ShiftReportResource::getUrl('create') . '?' . http_build_query(['employee_id' => $data['assignment']->employee_id, 'shift_assignment_id' => $data['assignment']->id]) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    Submit shift report
                </a>
            @endif
        </x-filament::section>
    </x-filament-widgets::widget>
@endif
