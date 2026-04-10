<?php

namespace App\Filament\Resources\PenaltyScheduleResource\Pages;

use App\Filament\Resources\PenaltyScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPenaltySchedule extends EditRecord
{
    protected static string $resource = PenaltyScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
