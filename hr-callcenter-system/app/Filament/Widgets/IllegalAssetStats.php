<?php

namespace App\Filament\Widgets;

use App\Models\IllegalAsset;
use App\Models\AssetEstimation;
use App\Models\AssetSale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IllegalAssetStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Confiscated Assets', IllegalAsset::count())
                ->description('All time registered illegal assets')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),
                
            Stat::make('Total Estimated Value', 'ETB ' . number_format(AssetEstimation::sum('estimated_value'), 2))
                ->description('Value of all estimated assets')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
                
            Stat::make('Total Sold Revenue', 'ETB ' . number_format(AssetSale::sum('sale_price'), 2))
                ->description('Revenue from sold assets')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Assets Disposed', IllegalAsset::where('status', 'Disposed')->count())
                ->description('Assets safely disposed')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}
