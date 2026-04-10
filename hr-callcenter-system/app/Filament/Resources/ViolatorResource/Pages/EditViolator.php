<?php

namespace App\Filament\Resources\ViolatorResource\Pages;

use App\Filament\Resources\ViolatorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditViolator extends EditRecord
{
    protected static string $resource = ViolatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
