<?php

namespace App\Filament\Resources\ShiftAssignmentResource\Pages;

use App\Filament\Resources\ShiftAssignmentResource;
use App\Models\Employee;
use App\Models\ShiftAssignment;
use App\Support\EthiopianDate;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateShiftAssignment extends CreateRecord
{
    protected static string $resource = ShiftAssignmentResource::class;

    public function mount(): void
    {
        parent::mount();

        $start = Carbon::parse(EthiopianDate::todayGregorianInAddisAbaba());
        $end = $start->copy()->addDays(29);
        $employeeId = request()->integer('employee_id');

        if ($employeeId) {
            $this->form->fill([
                'employee_id' => $employeeId,
                'assigned_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'status' => 'scheduled',
            ]);

            return;
        }

        // EC date picker opens on today’s Ethiopian day/month/year (wire value = today in Addis Ababa).
        $this->form->fill([
            'assigned_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['assigned_by'] = Auth::id();

        $employee = Employee::query()->find($data['employee_id'] ?? null);
        $assignedDate = $data['assigned_date'] ?? now('Africa/Addis_Ababa');

        if ($employee?->isOnApprovedLeave($assignedDate)) {
            throw ValidationException::withMessages([
                'employee_id' => 'This employee is on approved leave for the selected assignment start date.',
            ]);
        }

        return ShiftAssignment::create($data);
    }
}
