<?php

namespace App\Filament\Resources\VolunteerTipResource\Pages;

use App\Filament\Resources\VolunteerTipResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditVolunteerTip extends EditRecord
{
    protected static string $resource = VolunteerTipResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make()->visible(fn ($record) => $record->trashed()),
            RestoreAction::make()->visible(fn ($record) => $record->trashed()),
        ];
    }
}
