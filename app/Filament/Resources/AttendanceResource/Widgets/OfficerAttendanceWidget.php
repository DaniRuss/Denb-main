<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Filament\Resources\ShiftReportResource;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Support\EthiopianDate;
use App\Support\EthiopianTime;
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

    public ?string $halfDayReason = null;

    public bool $showLateCheckInModal = false;

    public bool $showCheckoutReasonModal = false;

    public bool $checkoutModalNeedsEarly = false;

    public bool $checkoutModalNeedsHalfDay = false;

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

        $today = EthiopianDate::todayGregorianInAddisAbaba();

        return ShiftAssignment::query()
            ->where('employee_id', $employee->id)
            ->whereDate('assigned_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
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

        return Attendance::findForShiftAssignmentToday($assignment);
    }

    public function getViewData(): array
    {
        if (! $this->isOfficer()) {
            return ['show' => false];
        }

        $now = now('Africa/Addis_Ababa');
        $employee = $this->getEmployee();
        $assignment = $this->getTodaysAssignment();
        $attendance = $assignment ? $this->getAttendance() : null;
        $withinShift = $assignment && $assignment->isWithinShift();
        $shiftWindow = $assignment ? $this->buildShiftWindow($assignment, $now) : null;
        $shiftNotStarted = $shiftWindow && $now->lessThan($shiftWindow['start']) && ! $withinShift;
        $shiftEnded = $shiftWindow && $now->greaterThan($shiftWindow['end']) && ! $withinShift;

        $checkedOut = $attendance && $attendance->check_out;
        $shiftEndedWithoutCheckIn = $shiftEnded && $attendance && ! $attendance->check_in;

        return [
            'show' => true,
            'employee' => $employee,
            'assignment' => $assignment,
            'attendance' => $attendance,
            'withinShift' => $withinShift,
            'shiftWindow' => $shiftWindow,
            'shiftNotStarted' => $shiftNotStarted,
            'shiftEnded' => $shiftEnded,
            'shiftEndedWithoutCheckIn' => $shiftEndedWithoutCheckIn,
            'canCheckIn' => $withinShift && (! $attendance || ! $attendance->check_in),
            'canCheckOut' => $withinShift && $attendance && $attendance->check_in && ! $attendance->check_out,
            'checkedOut' => $checkedOut,
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

        $at = Carbon::parse($at)->timezone('Africa/Addis_Ababa');

        $active = $assignment->shiftWindowForInstant($at);
        if ($active) {
            return $active;
        }

        $today = $at->copy()->startOfDay();
        $todayStr = $today->format('Y-m-d');
        $assignedStartStr = Carbon::parse($assignment->assigned_date)->format('Y-m-d');
        $assignedEndStr = Carbon::parse($assignment->end_date)->format('Y-m-d');

        if ($todayStr < $assignedStartStr || $todayStr > $assignedEndStr) {
            return null;
        }

        [$start, $end] = EthiopianTime::shiftWindowOnLocalDate($assignment->shift, $today);

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

    /**
     * Start check-in: if late, open modal; otherwise record immediately.
     */
    public function requestCheckIn(): void
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

        $attendance = Attendance::firstOrNewForShiftAssignmentToday($assignment);

        if ($attendance->check_in) {
            Notification::make()
                ->title('You are already checked in.')
                ->warning()
                ->send();

            return;
        }

        $shiftWindow = $this->buildShiftWindow($assignment, now('Africa/Addis_Ababa'));
        if (! $shiftWindow) {
            Notification::make()
                ->title('Unable to determine shift window.')
                ->danger()
                ->send();

            return;
        }

        $now = now('Africa/Addis_Ababa');
        $graceEnd = $shiftWindow['start']->copy()->addMinutes(Attendance::GRACE_MINUTES);

        if ($now->greaterThan($graceEnd)) {
            $this->lateReason = null;
            $this->showLateCheckInModal = true;

            return;
        }

        $this->performCheckIn(null);
    }

    public function cancelLateCheckInModal(): void
    {
        $this->showLateCheckInModal = false;
        $this->lateReason = null;
    }

    public function confirmLateCheckIn(): void
    {
        if (! filled(trim((string) $this->lateReason))) {
            Notification::make()
                ->title(__('Please explain why you are late.'))
                ->danger()
                ->send();

            return;
        }

        $this->performCheckIn(trim((string) $this->lateReason));
    }

    /**
     * @param  string|null  $lateReason  If set, appended to remarks as late check-in reason.
     */
    protected function performCheckIn(?string $lateReason): void
    {
        if (! $this->isOfficer()) {
            return;
        }

        $assignment = $this->getTodaysAssignment();
        if (! $assignment || ! $assignment->isWithinShift()) {
            return;
        }

        $attendance = Attendance::firstOrNewForShiftAssignmentToday($assignment);

        if ($attendance->check_in) {
            return;
        }

        $attendance->check_in = now('Africa/Addis_Ababa');
        $attendance->check_in_location = $this->checkInLocation ?: null;

        if ($lateReason !== null && $lateReason !== '') {
            $line = 'Late check-in: '.$lateReason;
            $existing = trim((string) $attendance->remarks);
            $attendance->remarks = $existing !== '' ? $existing."\n".$line : $line;
        }

        $attendance->save();

        $this->checkInLocation = null;
        $this->lateReason = null;
        $this->showLateCheckInModal = false;

        Notification::make()
            ->title('Check-in recorded successfully.')
            ->success()
            ->send();
    }

    /**
     * Start check-out: if early leave / half day, open modal; otherwise complete checkout.
     */
    public function requestCheckOut(): void
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

        $attendance = Attendance::findForShiftAssignmentToday($assignment);

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

        $now = now('Africa/Addis_Ababa');
        $shiftWindow = $this->buildShiftWindow($assignment, $now);

        if (! $shiftWindow) {
            Notification::make()
                ->title('Unable to determine shift window.')
                ->danger()
                ->send();

            return;
        }

        $isEarlyCheckout = $this->isEarlyCheckout($attendance->check_in, $now, $shiftWindow['end']);
        $isHalfDay = $attendance->previewAttendanceStatusAfterCheckout($now) === Attendance::STATUS_HALF_DAY;

        if ($isEarlyCheckout || $isHalfDay) {
            $this->checkoutModalNeedsEarly = $isEarlyCheckout;
            $this->checkoutModalNeedsHalfDay = $isHalfDay;
            $this->earlyCheckoutReason = null;
            $this->halfDayReason = null;
            $this->showCheckoutReasonModal = true;

            return;
        }

        $this->performCheckOut();
    }

    public function cancelCheckoutReasonModal(): void
    {
        $this->showCheckoutReasonModal = false;
        $this->earlyCheckoutReason = null;
        $this->halfDayReason = null;
    }

    public function confirmCheckoutReasons(): void
    {
        if ($this->checkoutModalNeedsEarly && ! filled(trim((string) $this->earlyCheckoutReason))) {
            Notification::make()
                ->title(__('Reason is required for early checkout.'))
                ->danger()
                ->send();

            return;
        }

        if ($this->checkoutModalNeedsHalfDay && ! filled(trim((string) $this->halfDayReason))) {
            Notification::make()
                ->title(__('Reason is required for half day.'))
                ->danger()
                ->send();

            return;
        }

        $this->performCheckOut();
    }

    protected function performCheckOut(): void
    {
        if (! $this->isOfficer()) {
            return;
        }

        $assignment = $this->getTodaysAssignment();
        if (! $assignment || ! $assignment->isWithinShift()) {
            return;
        }

        $attendance = Attendance::findForShiftAssignmentToday($assignment);

        if (! $attendance || ! $attendance->check_in || $attendance->check_out) {
            return;
        }

        $now = now('Africa/Addis_Ababa');
        $shiftWindow = $this->buildShiftWindow($assignment, $now);

        if (! $shiftWindow) {
            return;
        }

        $isEarlyCheckout = $this->isEarlyCheckout($attendance->check_in, $now, $shiftWindow['end']);
        $isHalfDay = $attendance->previewAttendanceStatusAfterCheckout($now) === Attendance::STATUS_HALF_DAY;

        $reasons = [];
        if ($isEarlyCheckout && filled(trim((string) $this->earlyCheckoutReason))) {
            $reasons[] = 'Early checkout: '.trim((string) $this->earlyCheckoutReason);
        }
        if ($isHalfDay && filled(trim((string) $this->halfDayReason))) {
            $reasons[] = 'Half day: '.trim((string) $this->halfDayReason);
        }

        if ($reasons !== []) {
            $existingRemarks = trim((string) $attendance->remarks);
            $attendance->remarks = $existingRemarks !== ''
                ? trim($existingRemarks."\n".implode("\n", $reasons))
                : implode("\n", $reasons);
        }

        $attendance->check_out = $now;
        $attendance->check_out_location = $this->checkOutLocation ?: null;
        $attendance->save();

        $this->checkOutLocation = null;
        $this->earlyCheckoutReason = null;
        $this->halfDayReason = null;
        $this->showCheckoutReasonModal = false;
        $this->checkoutModalNeedsEarly = false;
        $this->checkoutModalNeedsHalfDay = false;

        Notification::make()
            ->title('Check-out recorded. Redirecting to shift report…')
            ->success()
            ->send();

        $reportUrl = ShiftReportResource::getUrl('create').'?'.http_build_query([
            'employee_id' => $assignment->employee_id,
            'shift_assignment_id' => $assignment->id,
        ]);

        $this->redirect($reportUrl);
    }
}
