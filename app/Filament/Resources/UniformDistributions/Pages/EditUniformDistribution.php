<?php

namespace App\Filament\Resources\UniformDistributions\Pages;

use App\Filament\Resources\UniformDistributionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUniformDistribution extends EditRecord
{
    protected static string $resource = UniformDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
