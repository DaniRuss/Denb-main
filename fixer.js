const fs = require('fs');

const pageContent = `<?php

namespace App\\Filament\\Pages;

use Filament\\Pages\\Page;
use App\\Filament\\Widgets\\IntelligenceOverview;
use App\\Filament\\Widgets\\StrategyEfficiencyTable;
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

const chartContent = `<?php

namespace App\\Filament\\Widgets;

use App\\Models\\AwarenessEngagement;
use Filament\\Widgets\\ChartWidget;
use Illuminate\\Support\\Facades\\DB;

class StrategyEfficiencyChart extends ChartWidget
{
    protected static ?string $heading = 'Strategy Efficiency (ውጤታማነት በስትራቴጂ)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user = auth()->user();
        
        $query = AwarenessEngagement::query()->where('status', 'approved');
        
        if ($user->hasRole('woreda_coordinator')) {
            $query->where('woreda_id', $user->woreda_id);
        } elseif ($user->hasRole('paramilitary')) {
            $query->where('created_by', $user->id);
        }

        $data = (clone $query)
            ->select('engagement_type', DB::raw('SUM(COALESCE(headcount, 0) + COALESCE(org_headcount_male, 0) + COALESCE(org_headcount_female, 0)) as total'))
            ->groupBy('engagement_type')
            ->pluck('total', 'engagement_type')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => __('Reach by Strategy'),
                    'data' => array_values($data),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                ],
            ],
            'labels' => array_map(fn($k) => ucfirst(str_replace('_', ' ', $k)), array_keys($data)),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
`;

fs.writeFileSync('app/Filament/Pages/InformationLog.php', pageContent, 'utf8');
fs.writeFileSync('app/Filament/Widgets/StrategyEfficiencyChart.php', chartContent, 'utf8');
console.log('Fixed InformationLog.php and StrategyEfficiencyChart.php');
