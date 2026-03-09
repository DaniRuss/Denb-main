<?php

namespace App\Filament\Resources\UniformDistributions\Pages;

use App\Filament\Resources\UniformDistributionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUniformDistribution extends ViewRecord
{
    protected static string $resource = UniformDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
