<?php

namespace App\Filament\Resources\ShiftResource\Pages;

use App\Filament\Resources\ShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShifts extends ListRecords
{
    protected static string $resource = ShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(function (): bool {
                    $user = auth()->user();

                    if (! $user) {
                        return false;
                    }

                    // Officers and supervisors cannot create shift types.
                    if ($user->hasRole('officer') || $user->hasRole('supervisor')) {
                        return false;
                    }

                    return (bool) $user->can('manage_shifts');
                }),
        ];
    }
}
