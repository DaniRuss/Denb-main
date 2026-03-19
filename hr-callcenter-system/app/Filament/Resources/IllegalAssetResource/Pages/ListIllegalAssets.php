<?php

namespace App\Filament\Resources\IllegalAssetResource\Pages;

use App\Filament\Resources\IllegalAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIllegalAssets extends ListRecords
{
    protected static string $resource = IllegalAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
