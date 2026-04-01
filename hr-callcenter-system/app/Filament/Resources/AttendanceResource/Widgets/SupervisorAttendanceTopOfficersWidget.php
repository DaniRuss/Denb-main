<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupervisorAttendanceTopOfficersWidget extends Widget
{
    protected string $view = 'filament.resources.attendance-resource.widgets.supervisor-attendance-top-officers-widget';

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) ($user?->hasRole('supervisor'));
    }

    /**
     * @return array<int, int>
     */
    protected function officerIdsInSupervisorScope(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $supervisor = Employee::query()->where('user_id', $user->id)->first();
        if (! $supervisor) {
            return [];
        }

        return Employee::query()
            ->whereHas('user', fn ($q) => $q->role('officer'))
            ->where('id', '!=', $supervisor->id)
            ->when($supervisor->woreda_id, fn ($q) => $q->where('woreda_id', $supervisor->woreda_id))
            ->when(! $supervisor->woreda_id && $supervisor->sub_city_id, fn ($q) => $q->where('sub_city_id', $supervisor->sub_city_id))
            ->when(! $supervisor->woreda_id && ! $supervisor->sub_city_id, fn ($q) => $q->whereRaw('1 = 0'))
            ->pluck('id')
            ->all();
    }

    /**
     * @return array{name: string, code: string, count: int}|null
     */
    protected function topOfficerByStatuses(array $statuses): ?array
    {
        $ids = $this->officerIdsInSupervisorScope();
        if ($ids === []) {
            return null;
        }

        $row = Attendance::query()
            ->select('employee_id', DB::raw('COUNT(*) as c'))
            ->whereIn('employee_id', $ids)
            ->whereIn('attendance_status', $statuses)
            ->groupBy('employee_id')
            ->orderByDesc('c')
            ->orderBy('employee_id')
            ->first();

        if (! $row) {
            return null;
        }

        $employee = Employee::query()->find($row->employee_id);
        if (! $employee) {
            return null;
        }

        return [
            'name' => $employee->full_name_am,
            'code' => (string) $employee->employee_id,
            'count' => (int) $row->c,
        ];
    }

    /**
     * @return array{name: string, code: string, count: int}|null
     */
    protected function topAbsentOfficer(): ?array
    {
        return $this->topOfficerByStatuses([Attendance::STATUS_ABSENT]);
    }

    protected function topPresentOfficer(): ?array
    {
        return $this->topOfficerByStatuses([Attendance::STATUS_PRESENT]);
    }

    protected function topLateOfficer(): ?array
    {
        return $this->topOfficerByStatuses([Attendance::STATUS_LATE]);
    }

    protected function topHalfDayOfficer(): ?array
    {
        return $this->topOfficerByStatuses([Attendance::STATUS_HALF_DAY]);
    }

    public function getViewData(): array
    {
        return [
            'topAbsent' => $this->topAbsentOfficer(),
            'topPresent' => $this->topPresentOfficer(),
            'topLate' => $this->topLateOfficer(),
            'topHalfDay' => $this->topHalfDayOfficer(),
        ];
    }
}
