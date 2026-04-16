<?php

namespace App\Filament\Resources\Escalations\Pages;

use App\Filament\Resources\EscalationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEscalation extends ViewRecord
{
    protected static string $resource = EscalationResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
