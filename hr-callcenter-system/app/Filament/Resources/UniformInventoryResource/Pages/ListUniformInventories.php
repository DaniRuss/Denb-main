<?php
namespace App\Filament\Resources\UniformInventoryResource\Pages;
use App\Filament\Resources\UniformInventoryResource;
use App\Filament\Widgets\EmployeeUniformStatsWidget;
use App\Filament\Widgets\UniformSizeDistributionChart;
use App\Filament\Widgets\RecentUniformDistributionsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListUniformInventories extends ListRecords {
    protected static string $resource = UniformInventoryResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
    protected function getHeaderWidgets(): array {
        return [
            \App\Filament\Widgets\ParamilitaryUniformNeeds::class,
            EmployeeUniformStatsWidget::class,
            UniformSizeDistributionChart::class,
            RecentUniformDistributionsTable::class,
        ];
    }
}
