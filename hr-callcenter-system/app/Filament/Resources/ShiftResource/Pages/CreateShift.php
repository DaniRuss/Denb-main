<?php

namespace App\Filament\Resources\ShiftResource\Pages;

use App\Filament\Resources\ShiftResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShift extends CreateRecord
{
    protected static string $resource = ShiftResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Officers and supervisors are read-only for shift types.
        if ($user->hasRole('officer') || $user->hasRole('supervisor')) {
            return false;
        }

        return (bool) $user->can('manage_shifts');
    }
}
