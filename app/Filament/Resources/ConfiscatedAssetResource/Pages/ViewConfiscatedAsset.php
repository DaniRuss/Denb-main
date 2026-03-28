<?php

namespace App\Filament\Resources\ConfiscatedAssetResource\Pages;

use App\Filament\Resources\ConfiscatedAssetResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewConfiscatedAsset extends ViewRecord
{
    protected static string $resource = ConfiscatedAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
