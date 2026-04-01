@php
    $data = $this->getViewData();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('Top Officers Leaderboards (Woreda)') }}</x-slot>
        <x-slot name="description">{{ __('Separate Top 5 tables for absent, present, late, and half-day records.') }}</x-slot>

        @php
            $tables = [
                ['key' => 'topAbsent', 'title' => __('Top Absent'), 'countLabel' => __('Absent')],
                ['key' => 'topPresent', 'title' => __('Top Present'), 'countLabel' => __('Present')],
                ['key' => 'topLate', 'title' => __('Top Late'), 'countLabel' => __('Late')],
                ['key' => 'topHalfDay', 'title' => __('Top Half Day'), 'countLabel' => __('Half Day')],
            ];
        @endphp

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ($tables as $table)
                <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <p class="px-1 pb-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $table['title'] }}</p>

                    @if (count($data[$table['key']]) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-white/10">
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400">{{ __('#') }}</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400">{{ __('Officer') }}</th>
                                        <th class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-400">{{ __('Employee ID') }}</th>
                                        <th class="px-2 py-2 text-right font-medium text-gray-500 dark:text-gray-400">{{ $table['countLabel'] }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data[$table['key']] as $index => $row)
                                        <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                                            <td class="px-2 py-2 text-gray-700 dark:text-gray-300">{{ $index + 1 }}</td>
                                            <td class="px-2 py-2 font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                                            <td class="px-2 py-2 text-gray-700 dark:text-gray-300">{{ $row['code'] }}</td>
                                            <td class="px-2 py-2 text-right font-semibold text-gray-900 dark:text-white">{{ $row['count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="px-1 py-3 text-sm text-gray-500 dark:text-gray-400">{{ __('No records yet in this category.') }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
