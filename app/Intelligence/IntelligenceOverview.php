<?php
namespace App\Intelligence;

use App\Models\AwarenessEngagement;
use App\Models\Woreda;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class IntelligenceOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    // Polling every 30 seconds for real-time sync
    protected ?string $pollingInterval = '30s';

    public static function canView(): bool { return true; }

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        // Base approved query
        $base = AwarenessEngagement::where('status', 'approved');

        // Scope by role
        if ($user->hasRole('admin')) {
            $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
            $base->where('sub_city_id', $subCityId);
        } elseif ($user->hasRole('woreda_coordinator')) {
            $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
            $base->where('woreda_id', $woredaId);
        } elseif ($user->hasRole('paramilitary')) {
            $base->where('created_by', $user->id);
        }

        // Standardized reach formula
        $reachExpr = DB::raw("SUM(COALESCE(headcount,0) + COALESCE(org_headcount_male,0) + COALESCE(org_headcount_female,0))");

        $totalReach = (clone $base)->selectRaw(
            "SUM(COALESCE(headcount,0) + COALESCE(org_headcount_male,0) + COALESCE(org_headcount_female,0)) as total"
        )->value('total') ?? 0;

        $totalEngagements = (clone $base)->count();

        // Pending approvals
        $pendingCount = AwarenessEngagement::where('status', 'submitted')
            ->when($user->hasRole('admin'), fn($q) => $q->where('sub_city_id', \App\Helpers\JurisdictionHelper::getSubCityId($user)))
            ->when($user->hasRole('woreda_coordinator'), fn($q) => $q->where('woreda_id', \App\Helpers\JurisdictionHelper::getWoredaId($user)))
            ->when($user->hasRole('paramilitary'), fn($q) => $q->where('created_by', $user->id))
            ->count();

        // --- Role-specific stats ---

        if ($user->hasRole('super_admin')) {
            // 1. Total citizens reached (system-wide)
            $stats[] = Stat::make(__('Total Citizens Reached'), number_format($totalReach))
                ->icon('heroicon-m-users')
                ->color('success')
                ->description(__('All approved engagements'));

            // 2. Total approved sessions
            $stats[] = Stat::make(__('Approved Sessions'), number_format($totalEngagements))
                ->icon('heroicon-m-clipboard-document-check')
                ->color('info')
                ->description(__('Across all Woredas'));

            // 3. Pending approvals
            $stats[] = Stat::make(__('Pending Approvals'), number_format($pendingCount))
                ->icon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'gray')
                ->description(__('Awaiting review'));

            // 4. Active Sub-Cities (with at least one engagement)
            $activeSubCities = AwarenessEngagement::where('status', 'approved')
                ->distinct('sub_city_id')
                ->count('sub_city_id');
            $stats[] = Stat::make(__('Active Sub-Cities'), $activeSubCities)
                ->icon('heroicon-m-map')
                ->color('primary')
                ->description(__('Sub-cities with approved records'));

        } elseif ($user->hasRole('admin')) {
            // 1. Sub-city citizens reached
            $stats[] = Stat::make(__('Total Citizens Reached'), number_format($totalReach))
                ->icon('heroicon-m-users')
                ->color('success')
                ->description(__('Your sub-city engagements'));

            // 2. Sub-city approved sessions
            $stats[] = Stat::make(__('Approved Sessions'), number_format($totalEngagements))
                ->icon('heroicon-m-clipboard-document-check')
                ->color('info')
                ->description(__('Across your Woredas'));

            // 3. Pending approvals
            $stats[] = Stat::make(__('Pending Approvals'), number_format($pendingCount))
                ->icon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'gray')
                ->description($user->hasRole('admin') ? __('Awaiting your review') : __('Awaiting review'));

            // 4. Active Woredas in your sub-city
            $activeWoredas = AwarenessEngagement::where('status', 'approved')
                ->where('sub_city_id', \App\Helpers\JurisdictionHelper::getSubCityId($user))
                ->distinct('woreda_id')
                ->count('woreda_id');
            $stats[] = Stat::make(__('Active Woredas'), $activeWoredas)
                ->icon('heroicon-m-map-pin')
                ->color('primary')
                ->description(__('Within your sub-city'));

        } elseif ($user->hasRole('woreda_coordinator')) {
            // 1. My Woreda reach
            $stats[] = Stat::make(__('Woreda Citizens Reached'), number_format($totalReach))
                ->icon('heroicon-m-users')
                ->color('success')
                ->description(__('This Woreda — approved sessions'));

            // 2. My Woreda sessions
            $stats[] = Stat::make(__('Sessions in My Woreda'), number_format($totalEngagements))
                ->icon('heroicon-m-clipboard-document-check')
                ->color('info');

            // 3. Pending (in my Woreda)
            $stats[] = Stat::make(__('Pending in My Woreda'), number_format($pendingCount))
                ->icon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'gray')
                ->description(__('Submitted, not yet approved'));

            // 4. Officers active in this woreda
            $activeOfficers = AwarenessEngagement::where('status', 'approved')
                ->where('woreda_id', $user->woreda_id)
                ->distinct('created_by')
                ->count('created_by');
            $stats[] = Stat::make(__('Active Officers'), $activeOfficers)
                ->icon('heroicon-m-user-group')
                ->color('primary')
                ->description(__('Officers with approved sessions'));

        } elseif ($user->hasRole('paramilitary')) {
            // 1. My total reach
            $stats[] = Stat::make(__('My Citizens Reached'), number_format($totalReach))
                ->icon('heroicon-m-users')
                ->color('success')
                ->description(__('From your approved sessions'));

            // 2. My total sessions
            $stats[] = Stat::make(__('My Sessions'), number_format($totalEngagements))
                ->icon('heroicon-m-clipboard-document-check')
                ->color('info')
                ->description(__('Approved records submitted by you'));

            // 3. My pending
            $stats[] = Stat::make(__('My Pending'), number_format($pendingCount))
                ->icon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'gray')
                ->description(__('Submitted, awaiting approval'));

            // 4. My session this month
            $thisMonth = AwarenessEngagement::where('created_by', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $stats[] = Stat::make(__('Sessions This Month'), $thisMonth)
                ->icon('heroicon-m-calendar-days')
                ->color('primary');
        }

        return $stats;
    }
}