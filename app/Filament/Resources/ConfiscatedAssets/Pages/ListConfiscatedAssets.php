<?php

namespace App\Filament\Resources\ConfiscatedAssets\Pages;

use App\Filament\Resources\ConfiscatedAssets\ConfiscatedAssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConfiscatedAssets extends ListRecords
{
    protected static string $resource = ConfiscatedAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
