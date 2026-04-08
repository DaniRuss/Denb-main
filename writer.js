const fs = require('fs');
const content = `<?php

namespace App\\Filament\\Pages;

use Filament\\Pages\\Page;
use App\\Filament\\Widgets\\IntelligenceOverview;
use App\\Filament\\Widgets\StrategyEfficiencyTable;
use App\\Filament\\Widgets\\HierarchyPerformanceTable;

class InformationLog extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static string $view = 'filament.pages.information-log';

    public static function getNavigationGroup(): ?string
    {
        return __('Awareness Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Information Log');
    }

    public function getTitle(): string
    {
        return __('Information Log Intelligence');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->hasAnyRole(['super_admin', 'admin', 'woreda_coordinator', 'paramilitary']);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            IntelligenceOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            StrategyEfficiencyTable::class,
            HierarchyPerformanceTable::class,
        ];
    }
}
`;
fs.writeFileSync('app/Filament/Pages/InformationLog.php', content, 'utf8');
console.log('Success: InformationLog.php written without BOM.');
