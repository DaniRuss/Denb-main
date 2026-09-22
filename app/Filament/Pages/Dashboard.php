<?php

namespace App\Filament\Pages;

use App\Support\Filament\PanelAccess;
use App\Widgets\CaseManagementWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    public function getHeading(): string
    {
        $user = PanelAccess::user();

        if (! $user) {
            return 'Dashboard';
        }

        if (PanelAccess::hasRole('admin')) {
            return 'Admin Dashboard';
        }

        if (PanelAccess::hasRole('supervisor')) {
            return 'Supervisor Dashboard';
        }

        if (PanelAccess::hasRole('officer')) {
            return 'Officer Dashboard';
        }

        $role = $user->getRoleNames()->first();

        return $role ? ucfirst(str_replace('_', ' ', $role)) . ' Dashboard' : 'Dashboard';
    }

    public function getSubheading(): ?string
    {
        $user = PanelAccess::user();

        if (! $user) {
            return null;
        }

        return 'Only features allowed for your role are shown in the sidebar and dashboard.';
    }

    public function getWidgets(): array
    {
        $widgets = [
            AccountWidget::class,
        ];

        if (CaseManagementWidget::canView()) {
            $widgets[] = CaseManagementWidget::class;
        }

        return $widgets;
    }
}
