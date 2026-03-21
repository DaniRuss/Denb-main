<?php

namespace App\Filament\Resources\EscalationLevels\Pages;

use App\Filament\Resources\EscalationLevels\EscalationLevelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEscalationLevel extends EditRecord
{
    protected static string $resource = EscalationLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
