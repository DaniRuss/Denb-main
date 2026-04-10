<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\UniformDistribution;
use Filament\Widgets\ChartWidget;

class UniformSizeDistributionChart extends ChartWidget
{
    public function getHeading(): string
    {
        return 'Employee Size Distribution';
    }

    protected function getData(): array
    {
        $activeEmployees = Employee::active()->get();

        $shirtSizes = $activeEmployees->whereNotNull('shirt_size')->countBy('shirt_size')->sortKeys();
        $pantSizes = $activeEmployees->whereNotNull('pant_size')->countBy('pant_size')->sortKeys();

        return [
            'datasets' => [
                [
                    'label' => 'Shirt Sizes',
                    'data' => $shirtSizes->values()->toArray(),
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Pant Sizes',
                    'data' => $pantSizes->values()->toArray(),
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => array_merge(
                $shirtSizes->keys()->toArray(),
                $pantSizes->keys()->toArray()
            ),
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
