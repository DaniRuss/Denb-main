<?php

namespace App\Filament\Resources\ConfiscatedAssetResource\Pages;

use App\Filament\Resources\ConfiscatedAssetResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateConfiscatedAsset extends CreateRecord
{
    protected static string $resource = ConfiscatedAssetResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Confiscated asset recorded.')
            ->body('The asset has been logged and linked to the volunteer tip.')
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
