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
     * @param  array<int, string>  $statuses
     * @return array<int, array{name: string, code: string, count: int}>
     */
    protected function topOfficersByStatuses(array $statuses, int $limit = 5): array
    {
        $ids = $this->officerIdsInSupervisorScope();
        if ($ids === []) {
            return [];
        }

        $rows = Attendance::query()
            ->select('employee_id', DB::raw('COUNT(*) as c'))
            ->whereIn('employee_id', $ids)
            ->whereIn('attendance_status', $statuses)
            ->groupBy('employee_id')
            ->orderByDesc('c')
            ->orderBy('employee_id')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $employees = Employee::query()
            ->whereIn('id', $rows->pluck('employee_id')->all())
            ->get()
            ->keyBy('id');

        $result = [];

        foreach ($rows as $row) {
            $employee = $employees->get($row->employee_id);
            if (! $employee) {
                continue;
            }

            $result[] = [
                'name' => $employee->full_name_am,
                'code' => (string) $employee->employee_id,
                'count' => (int) $row->c,
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array{name: string, code: string, count: int}>
     */
    protected function topAbsentOfficers(): array
    {
        return $this->topOfficersByStatuses([Attendance::STATUS_ABSENT]);
    }

    /**
     * @return array<int, array{name: string, code: string, count: int}>
     */
    protected function topPresentOfficers(): array
    {
        return $this->topOfficersByStatuses([Attendance::STATUS_PRESENT]);
    }

    /**
     * @return array<int, array{name: string, code: string, count: int}>
     */
    protected function topLateOfficers(): array
    {
        return $this->topOfficersByStatuses([Attendance::STATUS_LATE]);
    }

    /**
     * @return array<int, array{name: string, code: string, count: int}>
     */
    protected function topHalfDayOfficers(): array
    {
        return $this->topOfficersByStatuses([Attendance::STATUS_HALF_DAY]);
    }

    public function getViewData(): array
    {
        return [
            'topAbsent' => $this->topAbsentOfficers(),
            'topPresent' => $this->topPresentOfficers(),
            'topLate' => $this->topLateOfficers(),
            'topHalfDay' => $this->topHalfDayOfficers(),
        ];
    }
}
