<?php
namespace App\Filament\Resources\QuarterlyReports\Pages;
use App\Filament\Resources\QuarterlyReportResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;
class ViewQuarterlyReport extends ViewRecord {
    protected static string $resource = QuarterlyReportResource::class;
    protected function getHeaderActions(): array { return [EditAction::make()]; }
}
