@php
    $data = $this->getViewData();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('Top Officers Performance (Woreda)') }}</x-slot>
        <x-slot name="description">{{ __('Leaderboard based on attendance history in your current scope.') }}</x-slot>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-danger-200 bg-danger-50 p-4 shadow-sm dark:border-danger-900/50 dark:bg-danger-950/30">
                <p class="text-xs font-semibold uppercase tracking-wide text-danger-700 dark:text-danger-300">{{ __('Top Absent') }}</p>
                @if($data['topAbsent'])
                    <p class="mt-2 text-base font-semibold text-gray-950 dark:text-white">{{ $data['topAbsent']['name'] }}</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ __('ID') }}: {{ $data['topAbsent']['code'] }}</p>
                    <p class="mt-2 text-xl font-bold text-danger-700 dark:text-danger-300">{{ $data['topAbsent']['count'] }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('absent records') }}</p>
                @else
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('No absent records yet.') }}</p>
                @endif
            </div>

            <div class="rounded-xl border border-success-200 bg-success-50 p-4 shadow-sm dark:border-success-900/50 dark:bg-success-950/30">
                <p class="text-xs font-semibold uppercase tracking-wide text-success-700 dark:text-success-300">{{ __('Top Present') }}</p>
                @if($data['topPresent'])
                    <p class="mt-2 text-base font-semibold text-gray-950 dark:text-white">{{ $data['topPresent']['name'] }}</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ __('ID') }}: {{ $data['topPresent']['code'] }}</p>
                    <p class="mt-2 text-xl font-bold text-success-700 dark:text-success-300">{{ $data['topPresent']['count'] }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('present records') }}</p>
                @else
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('No present records yet.') }}</p>
                @endif
            </div>

            <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 shadow-sm dark:border-warning-900/50 dark:bg-warning-950/30">
                <p class="text-xs font-semibold uppercase tracking-wide text-warning-700 dark:text-warning-300">{{ __('Top Late') }}</p>
                @if($data['topLate'])
                    <p class="mt-2 text-base font-semibold text-gray-950 dark:text-white">{{ $data['topLate']['name'] }}</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ __('ID') }}: {{ $data['topLate']['code'] }}</p>
                    <p class="mt-2 text-xl font-bold text-warning-700 dark:text-warning-300">{{ $data['topLate']['count'] }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('late records') }}</p>
                @else
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('No late records yet.') }}</p>
                @endif
            </div>

            <div class="rounded-xl border border-info-200 bg-info-50 p-4 shadow-sm dark:border-info-900/50 dark:bg-info-950/30">
                <p class="text-xs font-semibold uppercase tracking-wide text-info-700 dark:text-info-300">{{ __('Top Half Day') }}</p>
                @if($data['topHalfDay'])
                    <p class="mt-2 text-base font-semibold text-gray-950 dark:text-white">{{ $data['topHalfDay']['name'] }}</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ __('ID') }}: {{ $data['topHalfDay']['code'] }}</p>
                    <p class="mt-2 text-xl font-bold text-info-700 dark:text-info-300">{{ $data['topHalfDay']['count'] }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('half-day records') }}</p>
                @else
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('No half-day records yet.') }}</p>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
