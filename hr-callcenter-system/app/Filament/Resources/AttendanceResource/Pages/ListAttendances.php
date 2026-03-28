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
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    /**
     * Filament v5 list pages only embed the table in `content()` unless we also render header widgets here.
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->headerWidgets(Schema::make()),
                $this->getTabsContentComponent(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                EmbeddedTable::make(),
                RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
            ]);
    }

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
