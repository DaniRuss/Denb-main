<?php

namespace App\Filament\Resources\AwarenessEngagementResource\Pages;

use App\Filament\Resources\AwarenessEngagementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAwarenessEngagement extends ViewRecord
{
    protected static string $resource = AwarenessEngagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
