<?php

namespace App\Filament\Resources\VolunteerTipResource\Pages;

use App\Filament\Resources\VolunteerTipResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewVolunteerTip extends ViewRecord
{
    protected static string $resource = VolunteerTipResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
