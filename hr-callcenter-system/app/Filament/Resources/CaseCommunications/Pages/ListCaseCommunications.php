<?php

namespace App\Filament\Resources\CaseCommunications\Pages;

use App\Filament\Resources\CaseCommunications\CaseCommunicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCaseCommunications extends ListRecords
{
    protected static string $resource = CaseCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
