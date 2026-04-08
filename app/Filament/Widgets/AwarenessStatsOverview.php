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
        return auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin', 'woreda_coordinator', 'paramilitary']);
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        // 1. Campaigns (Global/Admin/Coordinator)
        $campaignQuery = \App\Models\Campaign::active();
        if ($user->hasRole('admin')) {
            $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
            $campaignQuery->where('sub_city_id', $subCityId);
        } elseif ($user->hasRole('woreda_coordinator')) {
            $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
            $campaignQuery->where('woreda_id', $woredaId);
        }
        $campaignCount = $campaignQuery->count();

        // 2. Pending Approvals (Scoped)
        $pendingQuery = AwarenessEngagement::where('status', 'submitted');
        if ($user->hasRole('admin')) {
            $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
            $pendingQuery->where('sub_city_id', $subCityId);
        } elseif ($user->hasRole('woreda_coordinator')) {
            $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
            $pendingQuery->where('woreda_id', $woredaId);
        }
        $pendingApprovals = $pendingQuery->count();

        // 3. Total Reach (Scoped)
        $reachQuery = AwarenessEngagement::where('status', 'approved');
        if ($user->hasRole('admin')) {
            $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
            $reachQuery->where('sub_city_id', $subCityId);
        } elseif ($user->hasRole('woreda_coordinator')) {
            $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
            $reachQuery->where('woreda_id', $woredaId);
        } elseif ($user->hasRole('paramilitary')) {
            $reachQuery->where('created_by', $user->id);
        }
        $totalReach = (clone $reachQuery)->select(\Illuminate\Support\Facades\DB::raw('SUM(COALESCE(headcount, 0) + COALESCE(org_headcount_male, 0) + COALESCE(org_headcount_female, 0)) as total'))->first()->total ?? 0;

        // 4. Personal Contributions (Paramilitary)
        $personalLogs = AwarenessEngagement::where('created_by', $user->id)->count();

        $stats = [
            Stat::make(__('Active Campaigns'), $campaignCount)
                ->description(__('Current active education programs'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('info'),

            Stat::make(__('Pending Approvals'), $pendingApprovals)
                ->description(__('Engagement logs awaiting review'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingApprovals > 0 ? 'warning' : 'success'),

            Stat::make(__('Total Reach (ዜጎች)'), number_format($totalReach))
                ->description(__('Individuals educated and engaged'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
        ];

        if ($user->hasRole('paramilitary')) {
            $stats[] = Stat::make(__('My Contributions'), $personalLogs)
                ->description(__('Your logged awareness sessions'))
                ->descriptionIcon('heroicon-m-user')
                ->color('primary');
        }

        return $stats;
    }
}
