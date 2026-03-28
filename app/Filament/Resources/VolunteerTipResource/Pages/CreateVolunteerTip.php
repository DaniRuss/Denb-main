<?php

namespace App\Filament\Resources\VolunteerTipResource\Pages;

use App\Filament\Resources\VolunteerTipResource;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\On;

class CreateVolunteerTip extends CreateRecord
{
    protected static string $resource = VolunteerTipResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\OfflineCreateWidget::class,
        ];
    }
}
