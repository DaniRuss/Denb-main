<?php

namespace App\Filament\Resources\VolunteerTipResource\Pages;

use App\Filament\Resources\VolunteerTipResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Livewire\Attributes\On;

class CreateVolunteerTip extends CreateRecord
{
    protected static string $resource = VolunteerTipResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
