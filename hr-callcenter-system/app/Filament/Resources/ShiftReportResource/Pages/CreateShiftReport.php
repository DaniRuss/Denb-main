<?php

namespace App\Filament\Resources\ShiftReportResource\Pages;

use App\Filament\Resources\ShiftReportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShiftReport extends CreateRecord
{
    protected static string $resource = ShiftReportResource::class;

    public function mount(): void
    {
        parent::mount();

        $employeeId = request()->query('employee_id');
        $shiftAssignmentId = request()->query('shift_assignment_id');
        if ($employeeId && $shiftAssignmentId) {
            $this->form->fill([
                'employee_id' => (int) $employeeId,
                'shift_assignment_id' => (int) $shiftAssignmentId,
            ]);
        }
    }
}
