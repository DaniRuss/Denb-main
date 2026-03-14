<?php

namespace App\Filament\Resources\ShiftAssignmentResource\Pages;

use App\Filament\Resources\ShiftAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShiftAssignments extends ListRecords
{
    protected static string $resource = ShiftAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
