<?php

namespace App\Filament\Resources\TipResource\Pages;

use App\Filament\Resources\TipResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTip extends ViewRecord
{
    protected static string $resource = TipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back to List')
                ->url(TipResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
