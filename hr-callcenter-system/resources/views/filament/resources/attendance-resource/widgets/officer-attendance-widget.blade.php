@php
    $data = $this->getViewData();
@endphp

@if($data['show'] ?? false)
    <x-filament-widgets::widget>
        <x-filament::section>
            @php
                $shiftStart = $data['shiftWindow']['start'] ?? null;
                $shiftEnd = $data['shiftWindow']['end'] ?? null;
            @endphp
            <x-slot name="heading">
                My attendance — {{ $data['assignment'] ? $data['assignment']->shift?->name . ' · ' . $data['assignment']->assigned_date?->format('M j, Y') : 'Today' }}
            </x-slot>

            @if(! $data['assignment'])
                <p class="text-gray-600 dark:text-gray-400">
                    You have no shift assigned for today. Check-in and check-out are available only on days you have a scheduled shift.
                </p>
            @elseif($data['shiftNotStarted'])
                <p class="text-amber-700 dark:text-amber-400">
                    Check-in is disabled until your shift starts ({{ $shiftStart?->format('g:i A') }} – {{ $shiftEnd?->format('g:i A') }}).
                </p>
                <x-filament::button type="button" color="gray" icon="heroicon-o-check-circle" disabled>
                    Check in
                </x-filament::button>
            @elseif($data['shiftEnded'])
                <p class="text-gray-600 dark:text-gray-400">
                    Your shift window has ended. Check-in and check-out are no longer available for this shift.
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

                    @if($data['requiresEarlyCheckoutReason'])
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Reason for early checkout
                            </label>
                            <textarea
                                wire:model.defer="earlyCheckoutReason"
                                rows="3"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-900 dark:border-gray-700"
                                placeholder="Enter your reason..."
                            ></textarea>
                        </div>
                    @endif

                    @if($data['requiresLateReason'])
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Reason for late check-in
                            </label>
                            <textarea
                                wire:model.defer="lateReason"
                                rows="3"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-900 dark:border-gray-700"
                                placeholder="Enter your reason..."
                            ></textarea>
                        </div>
                    @endif

                    <x-filament::button type="submit" color="primary" icon="heroicon-o-arrow-right-on-rectangle">
                        Check out & go to report
                    </x-filament::button>
                </form>
            @elseif(! $data['withinShift'])
                <p class="text-amber-700 dark:text-amber-400">
                    You can check in and check out only during your active shift window.
                </p>
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
