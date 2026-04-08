<?php
namespace App\Intelligence;

use Filament\Pages\Page;

class AwarenessLogAnalytic extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = "heroicon-o-presentation-chart-bar";
    protected static ?int $navigationSort = 3;

    // Non-static to match parent Filament\Pages\Page::$view
    protected string $view = "filament.pages.information-log";

    public static function getNavigationGroup(): ?string
    {
        return __("Awareness Management");
    }

    public static function getNavigationLabel(): string
    {
        return __("Information Log");
    }

    public function getTitle(): string
    {
        return __("Information Log Intelligence");
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->hasAnyRole(['super_admin', 'admin', 'woreda_coordinator', 'paramilitary', 'officer']);
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
            StrategyReachTable::class,
            AdministrativePerformanceTable::class,
        ];
    }
}