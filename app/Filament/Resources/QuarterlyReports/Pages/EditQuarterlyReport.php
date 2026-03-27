<?php
namespace App\Filament\Resources\QuarterlyReports\Pages;
use App\Filament\Resources\QuarterlyReportResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
class EditQuarterlyReport extends EditRecord {
    protected static string $resource = QuarterlyReportResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}
