<x-filament-panels::page>
    <style>
        .uniform-report-grid {
            display: grid;
            gap: 1.25rem;
        }

        .uniform-report-card {
            overflow: hidden;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        .uniform-report-card-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border-bottom: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 0.875rem 1rem;
        }

        .uniform-report-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .uniform-report-subtitle {
            margin: 0.125rem 0 0;
            font-size: 0.75rem;
            color: #64748b;
        }

        .uniform-report-total {
            border: 1px solid #f59e0b;
            border-radius: 999px;
            background: #fef3c7;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 700;
            color: #78350f;
        }

        .uniform-report-scroll {
            overflow-x: auto;
            padding: 0.75rem;
        }

        .uniform-report-table {
            width: 100%;
            min-width: 720px;
            border-collapse: collapse;
            border: 2px solid #475569;
            background: #ffffff;
            font-size: 0.875rem;
        }

        .uniform-report-table th,
        .uniform-report-table td {
            border: 1px solid #64748b !important;
            padding: 0.55rem 0.65rem;
            vertical-align: middle;
            white-space: nowrap;
            text-align: center;
            color: #0f172a;
        }

        .uniform-report-table thead th {
            background: #e2e8f0;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .uniform-report-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .uniform-report-table tbody tr:hover td {
            background: #e0f2fe;
        }

        .uniform-report-table tfoot td {
            background: #f1f5f9;
            font-weight: 800;
        }

        .uniform-report-table .uniform-report-group-cell {
            min-width: 160px;
            text-align: left;
            font-weight: 700;
        }

        .uniform-report-table .uniform-report-total-cell {
            background: #fef3c7;
            font-weight: 800;
            color: #78350f;
        }

        @media (prefers-color-scheme: dark) {
            .uniform-report-card {
                border-color: rgba(255, 255, 255, 0.2);
                background: #111827;
            }

            .uniform-report-card-header {
                border-color: rgba(255, 255, 255, 0.18);
                background: #1f2937;
            }

            .uniform-report-title,
            .uniform-report-table th,
            .uniform-report-table td {
                color: #f9fafb;
            }

            .uniform-report-subtitle {
                color: #cbd5e1;
            }

            .uniform-report-table {
                border-color: rgba(255, 255, 255, 0.35);
                background: #111827;
            }

            .uniform-report-table th,
            .uniform-report-table td {
                border-color: rgba(255, 255, 255, 0.3) !important;
            }

            .uniform-report-table thead th {
                background: #374151;
            }

            .uniform-report-table tbody tr:nth-child(even) td {
                background: #1f2937;
            }

            .uniform-report-table tbody tr:hover td {
                background: #0c4a6e;
            }

            .uniform-report-table tfoot td {
                background: #374151;
            }
        }
    </style>

    <form wire:submit.prevent class="space-y-4">
        {{ $this->form }}
    </form>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
                Employee uniform size report
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Grouped by {{ ($this->data['group_by'] ?? 'sub_city') === 'woreda' ? 'woreda' : 'sub city' }}. Each uniform type is shown in a separate table for easier review.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <x-filament::button type="button" color="gray" icon="heroicon-o-arrow-path" wire:click="resetFilters">
                Reset
            </x-filament::button>
            <x-filament::button type="button" icon="heroicon-o-arrow-down-tray" wire:click="exportCsv">
                Export CSV
            </x-filament::button>
        </div>
    </div>

    @php
        $uniformGroups = $this->uniformGroups;
        $rows = $this->rows;
        $totals = $this->totals;
    @endphp

    <div class="uniform-report-grid">
        @foreach($uniformGroups as $key => $group)
            <section class="uniform-report-card">
                <div class="uniform-report-card-header">
                    <div>
                        <h3 class="uniform-report-title">
                            {{ $group['label'] }}
                        </h3>
                        <p class="uniform-report-subtitle">
                            Size distribution by {{ ($this->data['group_by'] ?? 'sub_city') === 'woreda' ? 'woreda' : 'sub city' }}
                        </p>
                    </div>

                    <div class="uniform-report-total">
                        Total: {{ $totals['uniforms'][$key]['total'] }}
                    </div>
                </div>

                <div class="uniform-report-scroll">
                    <table class="uniform-report-table">
                        <thead>
                            <tr>
                                <th>
                                    No
                                </th>
                                <th class="uniform-report-group-cell">
                                    Group
                                </th>
                                <th>
                                    Employees
                                </th>
                                @foreach($group['sizes'] as $size)
                                    <th>
                                        {{ $size }}
                                    </th>
                                @endforeach
                                <th class="uniform-report-total-cell">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td>
                                        {{ $row['number'] }}
                                    </td>
                                    <td class="uniform-report-group-cell">
                                        {{ $row['name'] }}
                                    </td>
                                    <td>
                                        {{ $row['total'] }}
                                    </td>

                                    @foreach($group['sizes'] as $size)
                                        <td>
                                            {{ $row['uniforms'][$key][$size] }}
                                        </td>
                                    @endforeach

                                    <td class="uniform-report-total-cell">
                                        {{ $row['uniforms'][$key]['total'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($group['sizes']) + 4 }}" class="px-3 py-8 text-center text-gray-500">
                                        No employees match the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100 font-bold dark:bg-gray-800">
                                <td></td>
                                <td class="uniform-report-group-cell">
                                    {{ $totals['name'] }}
                                </td>
                                <td>
                                    {{ $totals['total'] }}
                                </td>

                                @foreach($group['sizes'] as $size)
                                    <td>
                                        {{ $totals['uniforms'][$key][$size] }}
                                    </td>
                                @endforeach

                                <td class="uniform-report-total-cell">
                                    {{ $totals['uniforms'][$key]['total'] }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
