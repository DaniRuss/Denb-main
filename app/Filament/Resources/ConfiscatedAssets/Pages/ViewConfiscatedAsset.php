<?php

namespace App\Filament\Resources\ConfiscatedAssets\Pages;

use App\Filament\Resources\ConfiscatedAssets\ConfiscatedAssetResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

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
