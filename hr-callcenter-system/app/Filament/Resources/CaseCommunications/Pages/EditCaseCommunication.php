<?php

namespace App\Filament\Resources\CaseCommunications\Pages;

use App\Filament\Resources\CaseCommunications\CaseCommunicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCaseCommunication extends EditRecord
{
    protected static string $resource = CaseCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
