<?php

namespace App\Filament\Resources\ViolationRecordResource\Pages;

use App\Filament\Resources\ViolationRecordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewViolationRecord extends ViewRecord
{
    protected static string $resource = ViolationRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
