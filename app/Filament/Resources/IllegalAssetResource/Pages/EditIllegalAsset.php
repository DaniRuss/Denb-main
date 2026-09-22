<?php

namespace App\Filament\Resources\IllegalAssetResource\Pages;

use App\Filament\Resources\IllegalAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIllegalAsset extends EditRecord
{
    protected static string $resource = IllegalAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
