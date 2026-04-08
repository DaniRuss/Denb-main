<?php

namespace App\Filament\Resources\PenaltyTypeResource\Pages;

use App\Filament\Resources\PenaltyTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenaltyTypes extends ListRecords
{
    protected static string $resource = PenaltyTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

