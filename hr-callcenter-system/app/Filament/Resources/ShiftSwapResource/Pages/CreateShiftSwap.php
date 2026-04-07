<?php

namespace App\Filament\Resources\ShiftSwapResource\Pages;

use App\Models\ShiftSwap;
use App\Filament\Resources\ShiftSwapResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateShiftSwap extends CreateRecord
{
    protected static string $resource = ShiftSwapResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $assignmentId = (int) ($data['shift_assignment_id'] ?? 0);
        if ($assignmentId && ShiftSwap::query()->where('shift_assignment_id', $assignmentId)->where('status', 'pending')->exists()) {
            throw ValidationException::withMessages([
                'shift_assignment_id' => __('There is already a pending swap request for this shift.'),
            ]);
        }
        return $data;
    }
}
