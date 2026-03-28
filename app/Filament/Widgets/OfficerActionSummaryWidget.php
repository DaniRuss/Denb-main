<?php

namespace App\Filament\Widgets;

use App\Models\VolunteerTip;
use App\Models\ConfiscatedAsset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OfficerActionSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin', 'officer']);
    }

    protected function getStats(): array
    {
        $financialPenalties = VolunteerTip::where('action_taken', 'financial_penalty')->count();
        $formalWarnings = VolunteerTip::where('action_taken', 'formal_warning')->count();
        $confiscatedCount = ConfiscatedAsset::count();
        $pendingActions = VolunteerTip::where('status', 'verified')->whereNull('action_taken')->count();

        return [
            Stat::make('Financial Penalties (የገንዘብ ቅጣት)', $financialPenalties)
                ->description('Fines issued based on tips')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),

            Stat::make('Formal Warnings (ማስጠንቀቂያ)', $formalWarnings)
                ->description('Official warnings issued')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Confiscated Assets (የተወረሰ ንብረት)', $confiscatedCount)
                ->description('Total items seized')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            Stat::make('Pending Enforcement (ውሳኔ የሚጠብቁ)', $pendingActions)
                ->description('Verified tips awaiting officer action')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
        ];
    }
}
