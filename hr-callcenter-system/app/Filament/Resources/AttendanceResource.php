<?php

namespace App\Filament\Resources;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Support\EthiopianDate;
use App\Support\EthiopianTime;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Shift Management';

    protected static ?string $navigationLabel = 'Attendance';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        if (! $user) {
            return $query;
        }

        // Officers should only ever see their own attendance.
        if ($user->hasRole('officer')) {
            $employee = Employee::query()->where('user_id', $user->id)->first();

            return $employee
                ? $query->where('employee_id', $employee->id)
                : $query->whereRaw('1 = 0');
        }

        // Supervisors should only ever see attendance within their sub_city/woreda,
        // for officer employees only.
        if ($user->hasRole('supervisor')) {
            $supervisor = Employee::query()->where('user_id', $user->id)->first();

            if (! $supervisor) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('employee', function (Builder $employeeQuery) use ($supervisor) {
                $employeeQuery->whereHas('user', fn ($q) => $q->role('officer'));

                if ($supervisor->sub_city_id) {
                    $employeeQuery->where('sub_city_id', $supervisor->sub_city_id);
                }

                if ($supervisor->woreda_id) {
                    $employeeQuery->where('woreda_id', $supervisor->woreda_id);
                }

                // Avoid showing the supervisor as an officer even if data overlaps.
                $employeeQuery->where('id', '!=', $supervisor->id);
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $employee = $user instanceof User
            ? Employee::query()->where('user_id', $user->id)->first()
            : null;
        $defaultEmployeeId = $employee?->id;

        return $schema->schema([
            Section::make('Attendance')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('Employee')
                        ->options(function () {
                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();

                            $query = Employee::query()
                                ->active()
                                ->orderBy('first_name_am');

                            if ($user && $user->hasRole('officer')) {
                                // Officers can only select themselves.
                                $query->where('user_id', $user->id);
                            }

                            if ($user && $user->hasRole('supervisor')) {
                                $supervisor = Employee::query()->where('user_id', $user->id)->first();

                                if ($supervisor) {
                                    if ($supervisor->sub_city_id) {
                                        $query->where('sub_city_id', $supervisor->sub_city_id);
                                    }

                                    if ($supervisor->woreda_id) {
                                        $query->where('woreda_id', $supervisor->woreda_id);
                                    }

                                    $query->whereHas('user', fn ($q) => $q->role('officer'));
                                    $query->where('id', '!=', $supervisor->id);
                                } else {
                                    $query->whereRaw('1 = 0');
                                }
                            }

                            return $query
                                ->get()
                                ->mapWithKeys(fn ($e) => [$e->id => $e->employee_id.' – '.$e->full_name_am])
                                ->all();
                        })
                        ->searchable()
                        ->default($defaultEmployeeId)
                        ->required()
                        ->live()
                        ->disabled(function () use ($defaultEmployeeId): bool {
                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();

                            return (bool) ($user?->hasRole('officer') && $defaultEmployeeId);
                        }),
                    Forms\Components\Select::make('shift_assignment_id')
                        ->label('Shift Assignment')
                        ->options(fn (Get $get) => ShiftAssignment::query()
                            ->when($get('employee_id'), fn ($q, $v) => $q->where('employee_id', $v))
                            ->whereIn('status', ['scheduled', 'completed'])
                            ->orderBy('assigned_date', 'desc')
                            ->get()
                            ->mapWithKeys(fn ($a) => [$a->id => (EthiopianDate::toEcYmdAmharic($a->assigned_date) ?? EthiopianDate::toEcYmd($a->assigned_date) ?? $a->assigned_date->format('Y-m-d')).' – '.$a->shift?->name.' (Block '.$a->block.')'])
                            ->all())
                        ->searchable()
                        ->required()
                        ->disabled(function () use ($defaultEmployeeId): bool {
                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();

                            return (bool) ($user?->hasRole('officer') && $defaultEmployeeId);
                        }),
                    Forms\Components\DateTimePicker::make('check_in')
                        ->label(__('Check in'))
                        ->ethiopic()
                        ->firstDayOfWeek(1)
                        ->seconds(false)
                        ->default(now('Africa/Addis_Ababa'))
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\DateTimePicker::make('check_out')
                        ->label(__('Check out'))
                        ->ethiopic()
                        ->firstDayOfWeek(1)
                        ->seconds(false),
                    Forms\Components\Textarea::make('remarks')->maxLength(1000)->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.employee_id')->label('Employee ID')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('employee.full_name_am')->label('Employee')->searchable(['first_name_am', 'last_name_am']),
                Tables\Columns\TextColumn::make('shiftAssignment.assigned_date')
                    ->label(__('Shift date (Ethiopian)'))
                    ->formatStateUsing(fn ($state) => EthiopianDate::toEcYmdAmharic($state) ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('Attendance date (EC)')
                    ->formatStateUsing(fn ($state) => EthiopianDate::toEcYmd($state) ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shiftAssignment.shift.name')->label('Shift'),
                Tables\Columns\TextColumn::make('check_in')
                    ->label(__('Check in (Ethiopian time)'))
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        $instant = $state instanceof Carbon
                            ? $state
                            : Carbon::parse($state)->timezone('Africa/Addis_Ababa');

                        $h24 = (int) $instant->format('G');
                        $m = (int) $instant->format('i');
                        [$ethHm] = EthiopianTime::from24Hour($h24, $m);

                        return $ethHm;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out')
                    ->label(__('Check out (Ethiopian time)'))
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '-';
                        }

                        $instant = $state instanceof Carbon
                            ? $state
                            : Carbon::parse($state)->timezone('Africa/Addis_Ababa');

                        $h24 = (int) $instant->format('G');
                        $m = (int) $instant->format('i');
                        [$ethHm] = EthiopianTime::from24Hour($h24, $m);

                        return $ethHm;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('attendance_status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'late' => 'warning',
                        'pending' => 'gray',
                        'half_day' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('remarks')
                    ->label('Reasons')
                    ->limit(80)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('attendance_status')
                    ->options([
                        'pending' => 'Pending',
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                        'half_day' => 'Half Day',
                    ]),
                Tables\Filters\Filter::make('more_absent_than_present')
                    ->label('More absent than present')
                    ->query(function (Builder $query): Builder {
                        return $query->whereRaw("
                            (
                                SELECT COUNT(*)
                                FROM attendances a2
                                WHERE a2.employee_id = attendances.employee_id
                                  AND a2.attendance_status = 'absent'
                            ) >
                            (
                                SELECT COUNT(*)
                                FROM attendances a3
                                WHERE a3.employee_id = attendances.employee_id
                                  AND a3.attendance_status IN ('present','late','half_day','overtime')
                            )
                        ");
                    }),
                Tables\Filters\Filter::make('more_present_than_absent')
                    ->label('More present than absent')
                    ->query(function (Builder $query): Builder {
                        return $query->whereRaw("
                            (
                                SELECT COUNT(*)
                                FROM attendances a2
                                WHERE a2.employee_id = attendances.employee_id
                                  AND a2.attendance_status IN ('present','late','half_day','overtime')
                            ) >
                            (
                                SELECT COUNT(*)
                                FROM attendances a3
                                WHERE a3.employee_id = attendances.employee_id
                                  AND a3.attendance_status = 'absent'
                            )
                        ");
                    }),
            ])
            ->modifyQueryUsing(function ($query) {
                /** @var \App\Models\User|null $user */
                $user = Auth::user();
                if ($user && $user->hasRole('officer')) {
                    $employee = Employee::query()->where('user_id', $user->id)->first();
                    if ($employee) {
                        // All daily rows for this officer (history while assignment is active or completed).
                        $query->where('employee_id', $employee->id)
                            ->whereHas('shiftAssignment', function (Builder $shiftAssignmentQuery) {
                                $shiftAssignmentQuery->whereIn('status', ['scheduled', 'completed']);
                            });
                    }
                }

                if ($user && $user->hasRole('supervisor')) {
                    $supervisor = Employee::query()->where('user_id', $user->id)->first();

                    if ($supervisor) {
                        $query->whereHas('employee', function (Builder $employeeQuery) use ($supervisor) {
                            $employeeQuery->whereHas('user', fn ($q) => $q->role('officer'));

                            if ($supervisor->sub_city_id) {
                                $employeeQuery->where('sub_city_id', $supervisor->sub_city_id);
                            }

                            if ($supervisor->woreda_id) {
                                $employeeQuery->where('woreda_id', $supervisor->woreda_id);
                            }

                            $employeeQuery->where('id', '!=', $supervisor->id);
                        });
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                }

                return $query;
            })
            ->actions([
                Action::make('check_in')
                    ->label('Check in')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function (Attendance $record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if (! $user?->hasRole('officer')) {
                            return false;
                        }

                        $assignment = $record->shiftAssignment;
                        $shift = $assignment?->shift;
                        if (! $assignment || ! $shift) {
                            return false;
                        }

                        if ($record->check_in) {
                            return false;
                        }

                        if (($assignment->status ?? null) !== 'scheduled') {
                            return false;
                        }

                        // Past today’s shift window end (repeats each day for the 30-day assignment).
                        $today = Carbon::parse(EthiopianDate::todayGregorianInAddisAbaba())->startOfDay();
                        $todayStr = $today->format('Y-m-d');
                        $assignedStartStr = Carbon::parse($assignment->assigned_date)->format('Y-m-d');
                        $assignedEndStr = Carbon::parse($assignment->end_date)->format('Y-m-d');
                        if ($todayStr < $assignedStartStr || $todayStr > $assignedEndStr) {
                            return false;
                        }

                        [, $end] = EthiopianTime::shiftWindowOnLocalDate($shift, $today);

                        return ! now('Africa/Addis_Ababa')->greaterThan($end);
                    })
                    ->disabled(function (Attendance $record): bool {
                        $assignment = $record->shiftAssignment;
                        if (! $assignment) {
                            return true;
                        }

                        // Disabled until shift becomes active.
                        return ! $assignment->isWithinShift();
                    })
                    ->action(function (Attendance $record): void {
                        $assignment = $record->shiftAssignment;

                        if (! $assignment || ! $assignment->isWithinShift()) {
                            Notification::make()
                                ->title('Check-in is available only during the active shift window.')
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($record->check_in) {
                            Notification::make()
                                ->title('Already checked in.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $record->check_in = Carbon::parse(now('Africa/Addis_Ababa'));
                        $record->check_in_location = null;
                        $record->save();

                        Notification::make()
                            ->title('Check-in recorded successfully.')
                            ->success()
                            ->send();
                    }),

                Action::make('check_out')
                    ->label('Check out & go to report')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('primary')
                    ->form([
                        Forms\Components\Textarea::make('earlyCheckoutReason')
                            ->label('Reason for early checkout')
                            ->rows(3)
                            ->maxLength(2000),
                        Forms\Components\Textarea::make('lateReason')
                            ->label('Reason for late check-in')
                            ->rows(3)
                            ->maxLength(2000),
                    ])
                    ->visible(function (Attendance $record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if (! $user?->hasRole('officer')) {
                            return false;
                        }

                        $assignment = $record->shiftAssignment;
                        if (! $assignment || ! $assignment->shift) {
                            return false;
                        }

                        if (! $record->check_in || $record->check_out) {
                            return false;
                        }

                        if (($assignment->status ?? null) !== 'scheduled') {
                            return false;
                        }

                        if (! $record->attendance_date || ! Carbon::parse($record->attendance_date)->isToday()) {
                            return false;
                        }

                        // Only show while shift is active.
                        return $assignment->isWithinShift();
                    })
                    ->action(function (Attendance $record, array $data) {
                        $assignment = $record->shiftAssignment;
                        $shift = $assignment?->shift;

                        if (! $assignment || ! $shift) {
                            return;
                        }

                        if (! $record->check_in || $record->check_out) {
                            return;
                        }

                        if (! $assignment->isWithinShift()) {
                            Notification::make()
                                ->title('Check-out is available only during the active shift window.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $now = now('Africa/Addis_Ababa');
                        $window = $assignment->shiftWindowForInstant($now);
                        if (! $window) {
                            Notification::make()
                                ->title('Check-out is available only during the active shift window.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $shiftStart = $window['start'];
                        $shiftEnd = $window['end'];

                        // Late means late check-in (beyond grace period).
                        $graceEnd = $shiftStart->copy()->addMinutes(\App\Models\Attendance::GRACE_MINUTES);
                        $isLate = $record->check_in->greaterThan($graceEnd);

                        // Early checkout check (mirrors Attendance's intent).
                        $workedHours = $now->diffInHours(Carbon::parse($record->check_in));
                        $isEarly = $now->lessThan($shiftEnd->copy()->subHours(\App\Models\Attendance::HALF_DAY_THRESHOLD_HOURS))
                            || $workedHours < \App\Models\Attendance::HALF_DAY_THRESHOLD_HOURS;

                        if ($isEarly && ! filled(trim((string) ($data['earlyCheckoutReason'] ?? '')))) {
                            Notification::make()
                                ->title('Early checkout reason is required.')
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($isLate && ! filled(trim((string) ($data['lateReason'] ?? '')))) {
                            Notification::make()
                                ->title('Late check-in reason is required.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $reasons = [];
                        if ($isEarly) {
                            $reasons[] = 'Early checkout: '.trim((string) $data['earlyCheckoutReason']);
                        }
                        if ($isLate) {
                            $reasons[] = 'Late check-in: '.trim((string) $data['lateReason']);
                        }

                        $existingRemarks = trim((string) $record->remarks);
                        $record->remarks = $existingRemarks !== ''
                            ? trim($existingRemarks."\n".implode("\n", $reasons))
                            : implode("\n", $reasons);

                        $record->check_out = Carbon::parse($now);
                        $record->check_out_location = null;
                        $record->save();

                        $reportUrl = ShiftReportResource::getUrl('create').'?'.http_build_query([
                            'employee_id' => $assignment->employee_id,
                            'shift_assignment_id' => $assignment->id,
                        ]);

                        Notification::make()
                            ->title('Check-out recorded. Redirecting to daily report...')
                            ->success()
                            ->send();

                        return redirect()->away($reportUrl);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AttendanceResource\Pages\ListAttendances::route('/'),
            'create' => \App\Filament\Resources\AttendanceResource\Pages\CreateAttendance::route('/create'),
            'edit' => \App\Filament\Resources\AttendanceResource\Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user && ($user->can('view_attendance') || $user->can('manage_attendance'));
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('manage_attendance');
    }

    public static function canEdit($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($record instanceof Attendance && $record->status_locked) {
            return $user->can('override_attendance_lock');
        }

        return $user->can('manage_attendance');
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($record instanceof Attendance && $record->status_locked) {
            return $user->can('override_attendance_lock');
        }

        return $user->can('manage_attendance');
    }
}