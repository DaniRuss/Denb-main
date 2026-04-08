<?php

namespace App\Filament\Resources\IncidentReportResource\Pages;

use App\Filament\Resources\IncidentReportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIncidentReport extends ViewRecord
{
    protected static string $resource = IncidentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

