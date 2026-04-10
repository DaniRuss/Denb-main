<?php

namespace App\Filament\Resources\PenaltyScheduleResource\Pages;

use App\Filament\Resources\PenaltyScheduleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPenaltySchedule extends ViewRecord
{
    protected static string $resource = PenaltyScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
