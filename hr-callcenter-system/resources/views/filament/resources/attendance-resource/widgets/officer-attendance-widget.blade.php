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
                @php
                    $headingEc = $data['assignment']?->assigned_date
                        ? \App\Support\EthiopianDate::toEcYmdAmharic($data['assignment']->assigned_date)
                        : \App\Support\EthiopianDate::toEcYmdAmharic(\Illuminate\Support\Carbon::now('Africa/Addis_Ababa'));
                @endphp
                {{ __('My attendance') }} — {{ $data['assignment'] ? $data['assignment']->shift?->name . ' · ' . ($headingEc ?? '') : ($headingEc ?? __('Today')) }}
            </x-slot>

            @if(! $data['assignment'])
                <p class="text-gray-600 dark:text-gray-400">
                    You have no shift assigned for today. Check-in and check-out are available only on days you have a scheduled shift.
                </p>
            @elseif($data['checkedOut'])
                <p class="text-gray-600 dark:text-gray-400">
                    {{ __('Check-out recorded at') }} {{ $data['attendance']->check_out ? \App\Support\EthiopianDate::toEcAmharicDateAndTime($data['attendance']->check_out) : '' }}
                    @if($data['attendance']->check_out_location)
                        · {{ $data['attendance']->check_out_location }}
                    @endif
                </p>
                <a href="{{ \App\Filament\Resources\ShiftReportResource::getUrl('create') . '?' . http_build_query(['employee_id' => $data['assignment']->employee_id, 'shift_assignment_id' => $data['assignment']->id]) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                    Submit shift report
                </a>
            @elseif($data['shiftNotStarted'])
                <p class="text-amber-700 dark:text-amber-400">
                    {{ __('Check-in is disabled until your shift starts') }} ({{ $shiftStart ? \App\Support\EthiopianDate::toEcAmharicDateAndTime($shiftStart) : '' }} – {{ $shiftEnd ? \App\Support\EthiopianDate::toEcAmharicDateAndTime($shiftEnd) : '' }}).
                </p>
                <x-filament::button type="button" color="gray" icon="heroicon-o-check-circle" disabled>
                    Check in
                </x-filament::button>
            @elseif($data['shiftEndedWithoutCheckIn'])
                <div class="rounded-lg border border-danger-200 bg-danger-50 p-4 dark:border-danger-900/50 dark:bg-danger-950/30">
                    <p class="text-sm font-semibold text-danger-800 dark:text-danger-200">
                        {{ __('Absent for today') }}
                    </p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                        {{ __('Your shift for today has ended and you did not check in. Status: Absent. Check-in is closed for this day.') }}
                    </p>
                    @if($shiftEnd)
                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                            {{ __('Shift window ended') }}: {{ \App\Support\EthiopianDate::toEcAmharicDateAndTime($shiftEnd) }}
                        </p>
                    @endif
                </div>
                <x-filament::button type="button" color="gray" icon="heroicon-o-check-circle" class="mt-3" disabled>
                    {{ __('Check in') }}
                </x-filament::button>
            @elseif($data['canCheckIn'])
                <form wire:submit.prevent="requestCheckIn" class="space-y-4">
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
                <form wire:submit.prevent="requestCheckOut" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Checked in at') }} {{ $data['attendance']->check_in ? \App\Support\EthiopianDate::toEcAmharicDateAndTime($data['attendance']->check_in) : '' }}
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
            @elseif($data['shiftEnded'])
                <p class="text-gray-600 dark:text-gray-400">
                    {{ __('Your shift window has ended. Check-in and check-out are no longer available for this shift.') }}
                </p>
            @elseif(! $data['withinShift'])
                <p class="text-amber-700 dark:text-amber-400">
                    You can check in and check out only during your active shift window.
                </p>
            @endif
        </x-filament::section>

        {{-- Late check-in: popup after clicking Check in --}}
        @if($showLateCheckInModal)
            <div
                class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-950/50 p-4 dark:bg-gray-950/80"
                wire:click="cancelLateCheckInModal"
                wire:key="late-checkin-modal"
            >
                <div
                    class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                    wire:click.stop
                >
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                        {{ __('You are checking in late') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Please explain why you are late. This will be saved on your attendance record.') }}
                    </p>
                    <div class="mt-4">
                        <label class="sr-only" for="late-reason-input">{{ __('Reason for late check-in') }}</label>
                        <textarea
                            id="late-reason-input"
                            wire:model.live="lateReason"
                            rows="4"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5"
                            placeholder="{{ __('Enter your reason…') }}"
                        ></textarea>
                    </div>
                    <div class="mt-6 flex flex-wrap justify-end gap-2">
                        <x-filament::button type="button" color="gray" wire:click="cancelLateCheckInModal">
                            {{ __('Cancel') }}
                        </x-filament::button>
                        <x-filament::button type="button" color="success" wire:click="confirmLateCheckIn" icon="heroicon-o-check-circle">
                            {{ __('Confirm check-in') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Early checkout / half day: popup after clicking Check out --}}
        @if($showCheckoutReasonModal)
            <div
                class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-950/50 p-4 dark:bg-gray-950/80"
                wire:click="cancelCheckoutReasonModal"
                wire:key="checkout-reason-modal"
            >
                <div
                    class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                    wire:click.stop
                >
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                        {{ __('Additional information required') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Please provide the reason(s) below. They will be saved on your attendance record.') }}
                    </p>

                    <div class="mt-4 space-y-4">
                        @if($checkoutModalNeedsEarly)
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300" for="early-checkout-reason-input">
                                    {{ __('Reason for early checkout') }}
                                </label>
                                <textarea
                                    id="early-checkout-reason-input"
                                    wire:model.live="earlyCheckoutReason"
                                    rows="3"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5"
                                    placeholder="{{ __('Enter your reason…') }}"
                                ></textarea>
                            </div>
                        @endif

                        @if($checkoutModalNeedsHalfDay)
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300" for="half-day-reason-input">
                                    {{ __('Reason for half day') }}
                                </label>
                                <textarea
                                    id="half-day-reason-input"
                                    wire:model.live="halfDayReason"
                                    rows="3"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5"
                                    placeholder="{{ __('Enter your reason…') }}"
                                ></textarea>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex flex-wrap justify-end gap-2">
                        <x-filament::button type="button" color="gray" wire:click="cancelCheckoutReasonModal">
                            {{ __('Cancel') }}
                        </x-filament::button>
                        <x-filament::button type="button" color="primary" wire:click="confirmCheckoutReasons" icon="heroicon-o-arrow-right-on-rectangle">
                            {{ __('Confirm check-out') }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif
    </x-filament-widgets::widget>
@endif
