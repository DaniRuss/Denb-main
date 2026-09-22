@php
    $data = $this->getViewData();
@endphp

@if($data['show'] ?? false)
    <x-filament-widgets::widget>
        <x-filament::section
            x-data="{
                locating: false,
                locationError: null,
                async formatLocation(position) {
                    const coords = position.coords;
                    const latitude = Number(coords.latitude).toFixed(6);
                    const longitude = Number(coords.longitude).toFixed(6);
                    const accuracy = coords.accuracy ? Math.round(coords.accuracy) : null;
                    const coordinateText = accuracy
                        ? `Lat: ${latitude}, Lng: ${longitude} (accuracy ${accuracy}m)`
                        : `Lat: ${latitude}, Lng: ${longitude}`;
                    const googleMapsUrl = `https://www.google.com/maps?q=${latitude},${longitude}`;

                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}`,
                            { headers: { Accept: 'application/json' } },
                        );

                        if (response.ok) {
                            const data = await response.json();
                            const address = data?.display_name;

                            if (address) {
                                return `${address} | ${coordinateText} | Google Maps: ${googleMapsUrl}`;
                            }
                        }
                    } catch (error) {
                        // Fall back to coordinates when reverse geocoding is unavailable.
                    }

                    return `${coordinateText} | Google Maps: ${googleMapsUrl}`;
                },
                async captureLocation(property, method) {
                    this.locationError = null;

                    if (! navigator.geolocation) {
                        this.locationError = 'Location is required, but it is not supported on this device or browser.';

                        return;
                    }

                    this.locating = true;
                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            await $wire.set(property, await this.formatLocation(position));
                            this.locating = false;
                            await $wire.call(method);
                        },
                        async (error) => {
                            this.locationError = `Location is required: ${error.message}`;
                            this.locating = false;
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0,
                        },
                    );
                },
            }"
        >
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
                <div class="space-y-4">
                    <template x-if="locationError">
                        <p class="text-sm text-danger-600 dark:text-danger-400" x-text="locationError"></p>
                    </template>
                    <x-filament::button
                        type="button"
                        color="success"
                        icon="heroicon-o-check-circle"
                        x-bind:disabled="locating"
                        x-on:click="captureLocation('checkInLocation', 'requestCheckIn')"
                    >
                        <span x-text="locating ? 'Getting location...' : 'Check in'"></span>
                    </x-filament::button>
                </div>
            @elseif($data['canCheckOut'])
                <div class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Checked in at') }} {{ $data['attendance']->check_in ? \App\Support\EthiopianDate::toEcAmharicDateAndTime($data['attendance']->check_in) : '' }}
                        @if($data['attendance']->check_in_location)
                            · {{ $data['attendance']->check_in_location }}
                        @endif
                    </p>
                    <template x-if="locationError">
                        <p class="text-sm text-danger-600 dark:text-danger-400" x-text="locationError"></p>
                    </template>

                    <x-filament::button
                        type="button"
                        color="primary"
                        icon="heroicon-o-arrow-right-on-rectangle"
                        x-bind:disabled="locating"
                        x-on:click="captureLocation('checkOutLocation', 'requestCheckOut')"
                    >
                        <span x-text="locating ? 'Getting location...' : 'Check out & go to report'"></span>
                    </x-filament::button>
                </div>
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
