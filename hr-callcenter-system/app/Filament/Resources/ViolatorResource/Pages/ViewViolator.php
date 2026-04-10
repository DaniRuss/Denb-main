<?php

namespace App\Filament\Resources\ViolatorResource\Pages;

use App\Filament\Resources\ViolatorResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewViolator extends ViewRecord
{
    protected static string $resource = ViolatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
