<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getMaxWidth(): Width|string|null
    {
        return Width::Full;
    }
}
