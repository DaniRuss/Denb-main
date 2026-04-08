<?php

namespace App\Filament\Resources\ShiftSwapResource\Pages;

use App\Filament\Resources\ShiftSwapResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShiftSwaps extends ListRecords
{
    protected static string $resource = ShiftSwapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
