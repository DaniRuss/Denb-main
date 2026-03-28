<?php

namespace App\Filament\Resources\VolunteerTipResource\Pages;

use App\Filament\Resources\VolunteerTipResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVolunteerTip extends CreateRecord
{
    protected static string $resource = VolunteerTipResource::class;

    /**
     * Display the offline "Save to Device" widget above the form.
     * Record type (volunteer_tip) is auto-detected within the blade from the URL.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\OfflineCreateWidget::class,
        ];
    }
}
