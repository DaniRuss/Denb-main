<?php

namespace App\Filament\Resources\EscalationLevels\Pages;

use App\Filament\Resources\EscalationLevels\EscalationLevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEscalationLevels extends ListRecords
{
    protected static string $resource = EscalationLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
