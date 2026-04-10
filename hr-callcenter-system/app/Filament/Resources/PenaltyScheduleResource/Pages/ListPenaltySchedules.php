<?php

namespace App\Filament\Resources\PenaltyScheduleResource\Pages;

use App\Filament\Resources\PenaltyScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenaltySchedules extends ListRecords
{
    protected static string $resource = PenaltyScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
