<?php

namespace App\Filament\Resources\ViolationRecordResource\Pages;

use App\Filament\Resources\ViolationRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditViolationRecord extends EditRecord
{
    protected static string $resource = ViolationRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
