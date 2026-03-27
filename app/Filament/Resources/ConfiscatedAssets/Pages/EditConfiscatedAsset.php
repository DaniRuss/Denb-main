<?php

namespace App\Filament\Resources\ConfiscatedAssets\Pages;

use App\Filament\Resources\ConfiscatedAssets\ConfiscatedAssetResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditConfiscatedAsset extends EditRecord
{
    protected static string $resource = ConfiscatedAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
