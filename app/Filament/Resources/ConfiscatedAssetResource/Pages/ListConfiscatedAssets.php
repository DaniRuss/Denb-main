<?php

namespace App\Filament\Resources\ConfiscatedAssetResource\Pages;

use App\Filament\Resources\ConfiscatedAssetResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListConfiscatedAssets extends ListRecords
{
    protected static string $resource = ConfiscatedAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
