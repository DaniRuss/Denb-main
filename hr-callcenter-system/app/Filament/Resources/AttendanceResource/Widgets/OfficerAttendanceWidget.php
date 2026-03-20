<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Filament\Resources\ShiftReportResource;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ShiftAssignment;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class OfficerAttendanceWidget extends Widget
{
    protected string $view = 'filament.resources.attendance-resource.widgets.officer-attendance-widget';

    protected static bool $isLazy = false;

    public ?string $checkInLocation = null;

    public ?string $checkOutLocation = null;

    public function mount(): void
    {
        if (! $this->isOfficer()) {
            return;
        }
    }

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('officer');
    }

    protected function isOfficer(): bool
    {
        return (bool) auth()->user()?->hasRole('officer');
    }

    protected function getEmployee(): ?Employee
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return null;
        }

        return Employee::query()->where('user_id', $user->id)->first();
    }

    /**
     * Today's shift assignment for the current employee (scheduled).
     */
    protected function getTodaysAssignment(): ?ShiftAssignment
    {
        $employee = $this->getEmployee();
        if (! $employee) {
            return null;
        }

        return ShiftAssignment::query()
            ->where('employee_id', $employee->id)
            ->where('assigned_date', Carbon::today())
            ->where('status', 'scheduled')
            ->with('shift')
            ->first();
    }

    protected function getAttendance(): ?Attendance
    {
        $assignment = $this->getTodaysAssignment();
        if (! $assignment) {
            return null;
        }

        return Attendance::query()
            ->where('employee_id', $assignment->employee_id)
            ->where('shift_assignment_id', $assignment->id)
            ->first();
    }

    public function getViewData(): array
    {
        if (! $this->isOfficer()) {
            return ['show' => false];
        }

        $employee = $this->getEmployee();
        $assignment = $this->getTodaysAssignment();
        $attendance = $assignment ? $this->getAttendance() : null;
        $withinShift = $assignment && $assignment->isWithinShift();

        return [
            'show' => true,
            'employee' => $employee,
            'assignment' => $assignment,
            'attendance' => $attendance,
            'withinShift' => $withinShift,
            'canCheckIn' => $withinShift && (! $attendance || ! $attendance->check_in),
            'canCheckOut' => $withinShift && $attendance && $attendance->check_in && ! $attendance->check_out,
            'checkedOut' => $attendance && $attendance->check_out,
        ];
    }

    public function checkIn(): void
    {
        if (! $this->isOfficer()) {
            return;
        }

        $assignment = $this->getTodaysAssignment();
        if (! $assignment || ! $assignment->isWithinShift()) {
            Notification::make()
                ->title('You can check in only during your shift.')
                ->danger()
                ->send();
            return;
        }

        $attendance = Attendance::firstOrNew([
            'employee_id' => $assignment->employee_id,
            'shift_assignment_id' => $assignment->id,
        ]);

        if ($attendance->check_in) {
            Notification::make()
                ->title('You are already checked in.')
                ->warning()
                ->send();
            return;
        }

        $attendance->check_in = now();
        $attendance->check_in_location = $this->checkInLocation ?: null;
        $attendance->save();

        $this->checkInLocation = null;

        Notification::make()
            ->title('Check-in recorded successfully.')
            ->success()
            ->send();
    }

    public function checkOut(): void
    {
        if (! $this->isOfficer()) {
            return;
        }

        $assignment = $this->getTodaysAssignment();
        if (! $assignment || ! $assignment->isWithinShift()) {
            Notification::make()
                ->title('You can check out only during your shift.')
                ->danger()
                ->send();
            return;
        }

        $attendance = Attendance::query()
            ->where('employee_id', $assignment->employee_id)
            ->where('shift_assignment_id', $assignment->id)
            ->first();

        if (! $attendance || ! $attendance->check_in) {
            Notification::make()
                ->title('You must check in first before checking out.')
                ->danger()
                ->send();
            return;
        }

        if ($attendance->check_out) {
            Notification::make()
                ->title('You are already checked out.')
                ->warning()
                ->send();
            return;
        }

        $attendance->check_out = now();
        $attendance->check_out_location = $this->checkOutLocation ?: null;
        $attendance->save();

        $this->checkOutLocation = null;

        Notification::make()
            ->title('Check-out recorded. Redirecting to shift report…')
            ->success()
            ->send();

        $reportUrl = ShiftReportResource::getUrl('create') . '?' . http_build_query([
            'employee_id' => $assignment->employee_id,
            'shift_assignment_id' => $assignment->id,
        ]);

        $this->redirect($reportUrl);
    }
}