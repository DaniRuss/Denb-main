<?php

namespace App\Filament\Resources\CaseAssignments\Pages;

use App\Filament\Resources\CaseAssignmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCaseAssignment extends ViewRecord
{
    protected static string $resource = CaseAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
