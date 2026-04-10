<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\UniformDistribution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeUniformStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeEmployees = Employee::active()->count();
        
        $shirtSizeDistribution = Employee::active()
            ->selectRaw('shirt_size, COUNT(*) as count')
            ->whereNotNull('shirt_size')
            ->groupBy('shirt_size')
            ->pluck('count', 'shirt_size')
            ->toArray();
        
        $pantSizeDistribution = Employee::active()
            ->selectRaw('pant_size, COUNT(*) as count')
            ->whereNotNull('pant_size')
            ->groupBy('pant_size')
            ->pluck('count', 'pant_size')
            ->toArray();

        $shoeCasualDistribution = Employee::active()
            ->selectRaw('shoe_size_casual, COUNT(*) as count')
            ->whereNotNull('shoe_size_casual')
            ->groupBy('shoe_size_casual')
            ->pluck('count', 'shoe_size_casual')
            ->toArray();

        $shoeLeatherDistribution = Employee::active()
            ->selectRaw('shoe_size_leather, COUNT(*) as count')
            ->whereNotNull('shoe_size_leather')
            ->groupBy('shoe_size_leather')
            ->pluck('count', 'shoe_size_leather')
            ->toArray();

        $totalDistributions = UniformDistribution::count();
        $recentDistributions = UniformDistribution::where('distribution_date', '>=', now()->subDays(30))->count();

        $shirtSizes = !empty($shirtSizeDistribution) ? implode(', ', array_keys($shirtSizeDistribution)) : 'N/A';
        $pantSizes = !empty($pantSizeDistribution) ? implode(', ', array_keys($pantSizeDistribution)) : 'N/A';
        $shoeCasualSizes = !empty($shoeCasualDistribution) ? implode(', ', array_keys($shoeCasualDistribution)) : 'N/A';
        $shoeLeatherSizes = !empty($shoeLeatherDistribution) ? implode(', ', array_keys($shoeLeatherDistribution)) : 'N/A';

        return [
            Stat::make('Active Employees', $activeEmployees)
                ->description('Total registered')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Shirt Sizes Needed', $shirtSizes)
                ->description(implode(', ', array_values($shirtSizeDistribution)) ?: '0 employees')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),

            Stat::make('Pant Sizes Needed', $pantSizes)
                ->description(implode(', ', array_values($pantSizeDistribution)) ?: '0 employees')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('success'),

            Stat::make('Casual Shoe Sizes', $shoeCasualSizes)
                ->description(implode(', ', array_values($shoeCasualDistribution)) ?: '0 employees')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),

            Stat::make('Leather Shoe Sizes', $shoeLeatherSizes)
                ->description(implode(', ', array_values($shoeLeatherDistribution)) ?: '0 employees')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('danger'),

            Stat::make('Uniforms Distributed', $totalDistributions)
                ->description($recentDistributions . ' in last 30 days')
                ->descriptionIcon('heroicon-m-truck')
                ->color('gray'),
        ];
    }
}
