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

    public ?string $earlyCheckoutReason = null;

    public ?string $lateReason = null;

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
            ->whereDate('assigned_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->where('status', 'scheduled')
            ->with('shift')
            ->orderByDesc('assigned_date')
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

        $now = now();
        $employee = $this->getEmployee();
        $assignment = $this->getTodaysAssignment();
        $attendance = $assignment ? $this->getAttendance() : null;
        $withinShift = $assignment && $assignment->isWithinShift();
        $shiftWindow = $assignment ? $this->buildShiftWindow($assignment, $now) : null;
        $shiftNotStarted = $shiftWindow && $now->lessThan($shiftWindow['start']) && ! $withinShift;
        $shiftEnded = $shiftWindow && $now->greaterThan($shiftWindow['end']) && ! $withinShift;

        $requiresEarlyCheckoutReason = false;
        $requiresLateReason = false;

        if ($withinShift && $attendance && $attendance->check_in && ! $attendance->check_out && $shiftWindow) {
            // We only request reasons before check-out is submitted.
            $requiresEarlyCheckoutReason = $this->isEarlyCheckout($attendance->check_in, $now, $shiftWindow['end']);
            $requiresLateReason = $this->isLateCheckIn($attendance->check_in, $shiftWindow['start']);
        }

        return [
            'show' => true,
            'employee' => $employee,
            'assignment' => $assignment,
            'attendance' => $attendance,
            'withinShift' => $withinShift,
            'shiftWindow' => $shiftWindow,
            'shiftNotStarted' => $shiftNotStarted,
            'shiftEnded' => $shiftEnded,
            'canCheckIn' => $withinShift && (! $attendance || ! $attendance->check_in),
            'canCheckOut' => $withinShift && $attendance && $attendance->check_in && ! $attendance->check_out,
            'checkedOut' => $attendance && $attendance->check_out,
            'requiresEarlyCheckoutReason' => $requiresEarlyCheckoutReason,
            'requiresLateReason' => $requiresLateReason,
        ];
    }

    /**
     * Build the shift window for the current day context.
     *
     * @return array{start: Carbon, end: Carbon}|null
     */
    protected function buildShiftWindow(ShiftAssignment $assignment, $at): ?array
    {
        if (! $assignment->shift) {
            return null;
        }

        $assignedStart = Carbon::parse($assignment->assigned_date)->startOfDay();
        $assignedEnd = Carbon::parse($assignment->end_date)->startOfDay();
        $date = Carbon::parse($at)->copy()->startOfDay();
        $clock = Carbon::parse($at);

        $startTime = Carbon::parse($assignment->shift->start_time);
        $endTime = Carbon::parse($assignment->shift->end_time);

        // For overnight shifts, early-morning punches belong to the previous shift day.
        $shiftStartDate = $date;
        if ($endTime->lessThanOrEqualTo($startTime)) {
            if ($clock->format('H:i:s') < $endTime->format('H:i:s')) {
                $shiftStartDate = $date->copy()->subDay();
            }
        }

        if ($shiftStartDate->lt($assignedStart) || $shiftStartDate->gt($assignedEnd)) {
            return null;
        }

        $start = Carbon::parse($shiftStartDate->format('Y-m-d') . ' ' . $assignment->shift->start_time);
        $end = Carbon::parse($shiftStartDate->format('Y-m-d') . ' ' . $assignment->shift->end_time);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    private function isLateCheckIn($checkIn, $shiftStart): bool
    {
        $checkIn = Carbon::parse($checkIn);
        $shiftStart = Carbon::parse($shiftStart);

        $graceEnd = $shiftStart->copy()->addMinutes(\App\Models\Attendance::GRACE_MINUTES);
        return $checkIn->greaterThan($graceEnd);
    }

    private function isEarlyCheckout($checkIn, $checkOut, $shiftEnd): bool
    {
        $checkIn = Carbon::parse($checkIn);
        $checkOut = Carbon::parse($checkOut);
        $shiftEnd = Carbon::parse($shiftEnd);

        $workedHours = $checkOut->diffInHours($checkIn);

        return $checkOut->lessThan($shiftEnd->copy()->subHours(\App\Models\Attendance::HALF_DAY_THRESHOLD_HOURS))
            || $workedHours < \App\Models\Attendance::HALF_DAY_THRESHOLD_HOURS;
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

        $now = now();
        $shiftWindow = $this->buildShiftWindow($assignment, $now);

        if (! $shiftWindow) {
            Notification::make()
                ->title('Unable to determine shift window.')
                ->danger()
                ->send();
            return;
        }

        $isEarlyCheckout = $this->isEarlyCheckout($attendance->check_in, $now, $shiftWindow['end']);
        $isLate = $this->isLateCheckIn($attendance->check_in, $shiftWindow['start']);

        if ($isEarlyCheckout && ! filled($this->earlyCheckoutReason)) {
            Notification::make()
                ->title('Reason is required for early checkout.')
                ->danger()
                ->send();
            return;
        }

        if ($isLate && ! filled($this->lateReason)) {
            Notification::make()
                ->title('Reason is required for late check-in.')
                ->danger()
                ->send();
            return;
        }

        // Attach reasons to the attendance record for supervisor visibility.
        $reasons = [];
        if ($isEarlyCheckout) {
            $reasons[] = 'Early checkout: ' . trim((string) $this->earlyCheckoutReason);
        }
        if ($isLate) {
            $reasons[] = 'Late check-in: ' . trim((string) $this->lateReason);
        }

        $existingRemarks = trim((string) $attendance->remarks);
        $attendance->remarks = $existingRemarks !== ''
            ? trim($existingRemarks . "\n" . implode("\n", $reasons))
            : implode("\n", $reasons);

        $attendance->check_out = $now;
        $attendance->check_out_location = $this->checkOutLocation ?: null;
        $attendance->save();

        $this->checkOutLocation = null;
        $this->earlyCheckoutReason = null;
        $this->lateReason = null;

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