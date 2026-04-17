<?php

namespace App\Filament\Widgets;

use App\Models\UniformDistribution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\UniformDistributions\Pages\ListUniformDistributions;
use Illuminate\Database\Eloquent\Builder;

class UniformDistributionStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static string $resource = ListUniformDistributions::class;

    protected function getTablePage(): string
    {
        return ListUniformDistributions::class;
    }

    protected function getStats(): array
    {
        $query = UniformDistribution::query();

        // Handle filtering if applicable (Filament handles this via InteractsWithPageTable)
        // However, we need to apply the same filters as the table.
        // If we are on the ListUniformDistributions page, Filament provides the table query.
        
        $tableQuery = $this->getPageTableQuery();
        
        if ($tableQuery) {
            $totalQty = (clone $tableQuery)->sum('quantity');
            $itemBreakdown = (clone $tableQuery)
                ->reorder()
                ->selectRaw('item_type, SUM(quantity) as total')
                ->groupBy('item_type')
                ->pluck('total', 'item_type')
                ->toArray();
        } else {
            $totalQty = $query->sum('quantity');
            $itemBreakdown = $query
                ->reorder()
                ->selectRaw('item_type, SUM(quantity) as total')
                ->groupBy('item_type')
                ->pluck('total', 'item_type')
                ->toArray();
        }

        $stats = [
            Stat::make('Total Items Distributed', $totalQty)
                ->description('Overall quantity')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
        ];

        $categories = [
            'shirt' => ['label' => 'Shirts', 'color' => 'info', 'icon' => 'heroicon-m-variable'],
            'pant' => ['label' => 'Pants', 'color' => 'warning', 'icon' => 'heroicon-m-list-bullet'],
            'shoe_casual' => ['label' => 'Casual Shoes', 'color' => 'primary', 'icon' => 'heroicon-m-sparkles'],
            'shoe_leather' => ['label' => 'Leather Shoes', 'color' => 'danger', 'icon' => 'heroicon-m-sparkles'],
        ];

        foreach ($categories as $type => $config) {
            if (isset($itemBreakdown[$type])) {
                $stats[] = Stat::make($config['label'], $itemBreakdown[$type])
                    ->description('Total distributed')
                    ->descriptionIcon($config['icon'])
                    ->color($config['color']);
            }
        }

        return $stats;
    }
}
