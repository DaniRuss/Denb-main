<?php

namespace App\Filament\Resources\ViolatorResource\Pages;

use App\Filament\Resources\ViolatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListViolators extends ListRecords
{
    protected static string $resource = ViolatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
