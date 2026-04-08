<?php

namespace App\Filament\Resources\ShiftSwapResource\Pages;

use App\Models\ShiftAssignment;
use App\Filament\Resources\ShiftSwapResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditShiftSwap extends EditRecord
{
    protected static string $resource = ShiftSwapResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['status'] ?? '', ['approved', 'rejected']) && empty($data['approved_by'])) {
            $data['approved_by'] = auth()->id();
        }

        $record = $this->getRecord();
        if (($data['status'] ?? '') === 'approved' && $record->status !== 'approved') {
            $assignment = ShiftAssignment::query()->find($data['shift_assignment_id'] ?? $record->shift_assignment_id);
            $employeeTo = (int) ($data['employee_to'] ?? $record->employee_to);
            if ($assignment && $assignment->employee_id == ($data['employee_from'] ?? $record->employee_from)) {
                $conflict = ShiftAssignment::query()
                    ->where('employee_id', $employeeTo)
                    ->whereDate('assigned_date', $assignment->assigned_date)
                    ->where('id', '!=', $assignment->id)
                    ->exists();
                if ($conflict) {
                    throw ValidationException::withMessages([
                        'status' => __('The receiving employee already has a shift assigned on this date.'),
                    ]);
                }
            }
        }

        return $data;
    }
}
