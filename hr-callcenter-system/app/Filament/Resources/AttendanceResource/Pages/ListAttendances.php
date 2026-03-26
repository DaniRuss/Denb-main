<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ShiftAssignment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    public function mount(): void
    {
        parent::mount();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user || ! $user->hasRole('officer')) {
            return;
        }

        $employee = Employee::query()->where('user_id', $user->id)->first();
        if (! $employee) {
            return;
        }

        // Ensure an attendance row exists for each scheduled shift assignment for today.
        $assignments = ShiftAssignment::query()
            ->where('employee_id', $employee->id)
            ->whereDate('assigned_date', Carbon::today())
            ->where('status', 'scheduled')
            ->get();

        foreach ($assignments as $assignment) {
            Attendance::query()->firstOrCreate([
                'employee_id' => $assignment->employee_id,
                'shift_assignment_id' => $assignment->id,
            ], [
                'check_in' => null,
                'check_out' => null,
                'attendance_status' => 'pending',
                'auto_generated' => false,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $isOfficer = $user && $user->hasRole('officer');

        return $isOfficer
            ? []
            : [Actions\CreateAction::make()];
    }

    protected function getHeaderWidgets(): array
    {
        // Officers should interact via the table actions only.
        return [];
    }
}
