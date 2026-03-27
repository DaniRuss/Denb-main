<?php
namespace App\Filament\Resources\QuarterlyReports\Pages;
use App\Filament\Resources\QuarterlyReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListQuarterlyReports extends ListRecords {
    protected static string $resource = QuarterlyReportResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
