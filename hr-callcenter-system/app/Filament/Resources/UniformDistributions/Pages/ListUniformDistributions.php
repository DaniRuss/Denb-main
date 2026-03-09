<?php

namespace App\Filament\Resources\UniformDistributions\Pages;

use App\Filament\Resources\UniformDistributionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUniformDistributions extends ListRecords
{
    protected static string $resource = UniformDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
