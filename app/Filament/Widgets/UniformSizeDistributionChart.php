<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UniformSizeDistributionChart extends ChartWidget
{
    public function getHeading(): string
    {
        return 'Employee Size Distribution';
    }

    protected function getData(): array
    {
        $shirtSizes = Employee::active()
            ->select('shirt_size', DB::raw('COUNT(*) as count'))
            ->whereNotNull('shirt_size')
            ->groupBy('shirt_size')
            ->orderBy('shirt_size')
            ->pluck('count', 'shirt_size');

        $pantSizes = Employee::active()
            ->select('pant_size', DB::raw('COUNT(*) as count'))
            ->whereNotNull('pant_size')
            ->groupBy('pant_size')
            ->orderBy('pant_size')
            ->pluck('count', 'pant_size');

        $labels = $shirtSizes
            ->keys()
            ->merge($pantSizes->keys())
            ->unique()
            ->sort()
            ->values();

        return [
            'datasets' => [
                [
                    'label' => 'Shirt Sizes',
                    'data' => $labels->map(fn (string $size): int => (int) ($shirtSizes[$size] ?? 0))->toArray(),
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Pant Sizes',
                    'data' => $labels->map(fn (string $size): int => (int) ($pantSizes[$size] ?? 0))->toArray(),
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
        ];
    }
}
