@php
    $data = $this->getViewData();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Top officers (your woreda / area)') }}
        </x-slot>
        <x-slot name="description">
            {{ __('Highest total attendance records in this list’s scope: absent vs. attended (present, late, half day, overtime).') }}
        </x-slot>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Most absent') }}</p>
                @if($data['topAbsent'])
                    <p class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
                        {{ $data['topAbsent']['name'] }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ __('ID') }}: {{ $data['topAbsent']['code'] }}
                        · <span class="font-medium text-danger-600 dark:text-danger-400">{{ $data['topAbsent']['count'] }}</span>
                        {{ __('absent day(s)') }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('No absent records yet for officers in your scope.') }}</p>
                @endif
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Most present (attended)') }}</p>
                @if($data['topPresent'])
                    <p class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
                        {{ $data['topPresent']['name'] }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ __('ID') }}: {{ $data['topPresent']['code'] }}
                        · <span class="font-medium text-success-600 dark:text-success-400">{{ $data['topPresent']['count'] }}</span>
                        {{ __('attended day(s)') }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('No attended records yet for officers in your scope.') }}</p>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
