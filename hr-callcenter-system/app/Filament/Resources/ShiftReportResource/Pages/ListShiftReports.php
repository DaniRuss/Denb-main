<?php

namespace App\Filament\Resources\ShiftReportResource\Pages;

use App\Filament\Resources\ShiftReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShiftReports extends ListRecords
{
    protected static string $resource = ShiftReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
