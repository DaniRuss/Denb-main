<?php

namespace App\Filament\Resources\ConfiscatedAssetResource\Pages;

use App\Filament\Resources\ConfiscatedAssetResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;

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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
