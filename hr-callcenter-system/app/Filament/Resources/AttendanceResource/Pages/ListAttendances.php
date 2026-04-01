<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Filament\Resources\AttendanceResource\Widgets\OfficerAttendanceWidget;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ShiftAssignment;
use App\Support\EthiopianDate;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\UniqueConstraintViolationException;
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

        // One row per assignment active “today” in Addis Ababa (same window as shift roster / officer widget).
        $today = EthiopianDate::todayGregorianInAddisAbaba();
        $assignments = ShiftAssignment::query()
            ->where('employee_id', $employee->id)
            ->whereDate('assigned_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', 'scheduled')
            ->get();

        foreach ($assignments as $assignment) {
            // Unique key after migration: (shift_assignment_id, attendance_date). Legacy DBs may still
            // have (employee_id, shift_assignment_id) only — then a second "day" insert fails; ignore.
            try {
                Attendance::query()->firstOrCreate(
                    [
                        'shift_assignment_id' => $assignment->id,
                        'attendance_date' => $today,
                    ],
                    [
                        'employee_id' => $assignment->employee_id,
                        'check_in' => null,
                        'check_out' => null,
                        'attendance_status' => 'pending',
                        'auto_generated' => false,
                    ]
                );
            } catch (UniqueConstraintViolationException) {
                // Row already exists under legacy unique index; do not rethrow.
            }
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
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user?->hasRole('officer')) {
            return [
                OfficerAttendanceWidget::class,
            ];
        }

        return [];
    }
}