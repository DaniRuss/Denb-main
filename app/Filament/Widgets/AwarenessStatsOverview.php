<?php

namespace App\Filament\Widgets;

use App\Models\AwarenessEngagement;
use App\Models\Campaign;
use App\Models\VolunteerTip;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AwarenessStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin', 'woreda_coordinator', 'paramilitary', 'officer']);
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        // 1. Campaigns (Global/Admin)
        $campaignCount = Campaign::active()->count();

        // 2. Pending Approvals (Scoped)
        $pendingQuery = AwarenessEngagement::where('status', 'submitted');
        if ($user->hasRole('woreda_coordinator')) {
            $pendingQuery->where('woreda_id', $user->woreda_id);
        }
        $pendingApprovals = $pendingQuery->count();

        // 3. Verified Tips (Scoped)
        $verifiedTipsQuery = VolunteerTip::verified();
        if ($user->hasRole('woreda_coordinator')) {
            $verifiedTipsQuery->where('woreda_id', $user->woreda_id);
        }
        $verifiedTipsCount = $verifiedTipsQuery->count();

        // 4. Personal Contributions (Paramilitary)
        $personalLogs = AwarenessEngagement::where('created_by', $user->id)->count();

        $stats = [
            Stat::make('Active Campaigns (ዘመቻዎች)', $campaignCount)
                ->description('Current active education programs')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('info'),

            Stat::make('Pending Approvals (ምዝገባዎች)', $pendingApprovals)
                ->description('Engagement logs awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingApprovals > 0 ? 'warning' : 'success'),

            Stat::make('Verified Tips (ጥቆማዎች)', $verifiedTipsCount)
                ->description('Verified community tips for action')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];

        if ($user->hasRole('paramilitary')) {
            $stats[] = Stat::make('My Contributions', $personalLogs)
                ->description('Your logged awareness sessions')
                ->descriptionIcon('heroicon-m-user')
                ->color('primary');
        }

        return $stats;
    }
}
