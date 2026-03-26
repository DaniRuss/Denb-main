<?php

namespace App\Filament\Resources\ShiftAssignmentResource\Pages;

use App\Filament\Resources\ShiftAssignmentResource;
use App\Models\ShiftAssignment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateShiftAssignment extends CreateRecord
{
    protected static string $resource = ShiftAssignmentResource::class;

    public function mount(): void
    {
        parent::mount();

        $employeeId = request()->integer('employee_id');
        if (! $employeeId) {
            return;
        }

        $start = Carbon::today();
        $end = $start->copy()->addDays(29);

        $this->form->fill([
            'employee_id' => $employeeId,
            'assigned_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'end_date_display' => $end->toDateString(),
            'status' => 'scheduled',
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['assigned_by'] = Auth::id();

        return ShiftAssignment::create($data);
    }
}
