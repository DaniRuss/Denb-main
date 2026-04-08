<?php

namespace App\Filament\Resources\ActionTypeResource\Pages;

use App\Filament\Resources\ActionTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActionTypes extends ListRecords
{
    protected static string $resource = ActionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

