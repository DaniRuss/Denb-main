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
        $user = auth()->user();
        $tipQuery = VolunteerTip::query();

        if ($user->hasRole('admin')) {
            $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
            $tipQuery->where('sub_city_id', $subCityId);
        } elseif ($user->hasRole('woreda_coordinator')) {
            $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
            $tipQuery->where('woreda_id', $woredaId);
        } elseif ($user->hasRole('officer')) {
            // Enforcement officers see tips in their assigned jurisdiction
            $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
            if ($woredaId) {
                $tipQuery->where('woreda_id', $woredaId);
            } else {
                $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
                $tipQuery->where('sub_city_id', $subCityId);
            }
        }

        $financialPenalties = (clone $tipQuery)->where('action_taken', 'financial_penalty')->count();
        $formalWarnings = (clone $tipQuery)->where('action_taken', 'formal_warning')->count();
        $pendingActions = (clone $tipQuery)->where('status', 'verified')->whereNull('action_taken')->count();

        return [
            Stat::make('Financial Penalties (የገንዘብ ቅጣት)', $financialPenalties)
                ->description('Fines issued based on tips')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),

            Stat::make('Formal Warnings (ማስጠንቀቂያ)', $formalWarnings)
                ->description('Official warnings issued')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Pending Enforcement (ውሳኔ የሚጠብቁ)', $pendingActions)
                ->description('Verified tips awaiting officer action')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
        ];
    }
}
