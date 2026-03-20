<?php

namespace App\Filament\Resources;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Support\EthiopianDate;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftAssignmentResource extends Resource
{
    protected static ?string $model = ShiftAssignment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Shift Management';

    protected static ?string $navigationLabel = 'Shift Assignment';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        if (! $user) {
            return $query;
        }

        // Officers should only ever see their own assignments.
        if ($user->hasRole('officer')) {
            $employee = Employee::query()->where('user_id', $user->id)->first();
            return $employee
                ? $query->where('employee_id', $employee->id)
                : $query->whereRaw('1 = 0');
        }

        // Supervisors should see all officers in their sub_city/woreda, even if
        // they do not currently have a scheduled assignment.
        if ($user->hasRole('supervisor')) {
            $supervisor = Employee::query()->where('user_id', $user->id)->first();
            if ($supervisor) {
                $today = now()->toDateString();

                return ShiftAssignment::query()
                    ->from('employees')
                    ->leftJoin('shift_assignments', function ($join) use ($today) {
                        $join->on('shift_assignments.employee_id', '=', 'employees.id')
                            ->where('shift_assignments.status', '=', 'scheduled')
                            ->whereDate('shift_assignments.assigned_date', '<=', $today)
                            ->whereDate('shift_assignments.end_date', '>=', $today);
                    })
                    ->select([
                        DB::raw('CASE WHEN shift_assignments.id IS NULL THEN -employees.id ELSE shift_assignments.id END as id'),
                        'shift_assignments.id as assignment_id',
                        'employees.id as employee_id',
                        'shift_assignments.shift_id',
                        'shift_assignments.zone',
                        'shift_assignments.assigned_date',
                        'shift_assignments.end_date',
                        'shift_assignments.assigned_by',
                        DB::raw("CASE WHEN shift_assignments.id IS NULL THEN 'unassigned' ELSE 'assigned' END as status"),
                    ])
                    ->where('employees.id', '!=', $supervisor->id)
                    ->where('employees.status', 'active')
                    ->when($supervisor->sub_city_id, fn ($q, $v) => $q->where('employees.sub_city_id', $v))
                    ->when($supervisor->woreda_id, fn ($q, $v) => $q->where('employees.woreda_id', $v))
                    ->join('users', 'users.id', '=', 'employees.user_id')
                    ->join('model_has_roles', function ($join) {
                        $join->on('model_has_roles.model_id', '=', 'users.id')
                            ->where('model_has_roles.model_type', User::class);
                    })
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', 'officer')
                    ->distinct();
            }
        }

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $query = parent::getRecordRouteBindingEloquentQuery();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('officer')) {
            $employee = Employee::query()->where('user_id', $user->id)->first();

            return $employee
                ? $query->where('shift_assignments.employee_id', $employee->id)
                : $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('supervisor')) {
            $supervisor = Employee::query()->where('user_id', $user->id)->first();

            if (! $supervisor) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('employee', function ($q) use ($supervisor) {
                $q->where('id', '!=', $supervisor->id)
                    ->where('status', 'active')
                    ->when($supervisor->sub_city_id, fn ($sq, $v) => $sq->where('sub_city_id', $v))
                    ->when($supervisor->woreda_id, fn ($sq, $v) => $sq->where('woreda_id', $v))
                    ->whereHas('user', fn ($uq) => $uq->role('officer'));
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Assignment')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('Employee')
                        ->options(function () {
                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();
                            $query = Employee::query()
                                ->active()
                                ->orderBy('first_name_am');

                            // Always hide officers who already have an active 30-day assignment (scheduled).
                            $query->whereDoesntHave('shiftAssignments', function ($q) {
                                $today = now()->toDateString();
                                $q->where('status', 'scheduled')
                                  ->whereDate('assigned_date', '<=', $today)
                                  ->whereDate('end_date', '>=', $today);
                            });

                            // If user is a supervisor, filter employees by their sub_city/woreda (officers only).
                            if ($user && $user->hasRole('supervisor')) {
                                $supervisor = Employee::where('user_id', $user->id)->first();

                                if ($supervisor) {
                                    if ($supervisor->sub_city_id) {
                                        $query->where('sub_city_id', $supervisor->sub_city_id);
                                    }

                                    if ($supervisor->woreda_id) {
                                        $query->where('woreda_id', $supervisor->woreda_id);
                                    }

                                    $query
                                        ->where('id', '!=', $supervisor->id)
                                        ->whereHas('user', fn ($q) => $q->role('officer'));
                                }
                            }
                            
                            return $query->get()
                                ->mapWithKeys(fn ($e) => [$e->id => $e->employee_id . ' - ' . $e->full_name_am])
                                ->all();
                        })
                        ->searchable()
                        ->required()
                        ->live(),
                    Forms\Components\Select::make('shift_id')
                        ->label('Shift')
                        ->relationship('shift', 'name')
                        ->options(
                            Shift::query()
                                ->where('is_active', true)
                                ->orderBy('start_time')
                                ->pluck('name', 'id')
                                ->all()
                        )
                        ->required(),
                    Forms\Components\TextInput::make('zone')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\Select::make('assigned_ec_year')
                        ->label('Start year (EC)')
                        ->options(function () {
                            $ecNow = EthiopianDate::toEcYmd(now());
                            $y = EthiopianDate::splitEcYmd($ecNow)['y'] ?? 2018;

                            $options = [];
                            foreach (range($y - 1, $y + 2) as $year) {
                                $options[(string) $year] = (string) $year;
                            }

                            return $options;
                        })
                        ->searchable()
                        ->live()
                        ->afterStateHydrated(function ($state, callable $set, ?ShiftAssignment $record) {
                            if (! $record?->assigned_date) {
                                return;
                            }

                            $parts = EthiopianDate::splitEcYmd(EthiopianDate::toEcYmd($record->assigned_date));
                            if ($parts) {
                                $set('assigned_ec_year', (string) $parts['y']);
                                $set('assigned_ec_month', (string) $parts['m']);
                                $set('assigned_ec_day', (string) $parts['d']);
                            }

                            if ($record?->end_date) {
                                $set('end_date_ec', EthiopianDate::toEcYmd($record->end_date));
                            }
                        }),
                    Forms\Components\Select::make('assigned_ec_month')
                        ->label('Start month (EC)')
                        ->options(collect(EthiopianDate::MONTHS_AM)->mapWithKeys(fn ($name, $num) => [(string) $num => $name])->all())
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            // reset day when month changes
                            $set('assigned_ec_day', null);
                        }),
                    Forms\Components\Select::make('assigned_ec_day')
                        ->label('Start day (EC)')
                        ->options(function (callable $get) {
                            $y = (int) ($get('assigned_ec_year') ?: 0);
                            $m = (int) ($get('assigned_ec_month') ?: 0);

                            if (! $y || ! $m) {
                                return [];
                            }

                            $max = EthiopianDate::daysInEcMonth($y, $m);
                            $options = [];
                            foreach (range(1, $max) as $d) {
                                $options[(string) $d] = (string) $d;
                            }

                            return $options;
                        })
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $y = (int) ($get('assigned_ec_year') ?: 0);
                            $m = (int) ($get('assigned_ec_month') ?: 0);
                            $d = (int) ($state ?: 0);

                            if (! $y || ! $m || ! $d) {
                                $set('assigned_date', null);
                                $set('end_date', null);
                                $set('end_date_ec', null);
                                return;
                            }

                            $ec = sprintf('%04d-%02d-%02d', $y, $m, $d);
                            $start = EthiopianDate::fromEcYmd($ec);
                            $end = $start->copy()->addDays(29);

                            $set('assigned_date', $start->toDateString());
                            $set('end_date', $end->toDateString());
                            $set('end_date_ec', EthiopianDate::toEcYmd($end));
                        })
                        ->required(),
                    Forms\Components\TextInput::make('end_date_ec')
                        ->label('End date (EC)')
                        ->disabled(),
                    Forms\Components\Hidden::make('assigned_date')->required(),
                    Forms\Components\Hidden::make('end_date')->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'scheduled' => 'Scheduled',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            'no_show' => 'No Show',
                        ])
                        ->default('scheduled')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.employee_id')->label('Employee ID')->searchable(),
                Tables\Columns\TextColumn::make('employee.full_name_am')->label('Employee')->searchable(['first_name_am', 'last_name_am']),
                Tables\Columns\TextColumn::make('shift.name')->sortable(),
                Tables\Columns\TextColumn::make('zone')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('assigned_date')
                    ->label('Start (EC)')
                    ->formatStateUsing(fn ($state) => EthiopianDate::toEcYmd($state) ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End (EC)')
                    ->formatStateUsing(fn ($state) => EthiopianDate::toEcYmd($state) ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'assigned' => 'success',
                        'unassigned' => 'warning',
                        'scheduled' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'gray',
                        'no_show' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('assignedBy.name')->label('Assigned by')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('employee_id')
            ->recordUrl(function (ShiftAssignment $record): ?string {
                if ((int) $record->id <= 0) {
                    return null;
                }

                return static::getUrl('edit', ['record' => $record]);
            })
            ->filters([
                Tables\Filters\SelectFilter::make('assignment_state')
                    ->label('Assignment Status')
                    ->options([
                        'assigned' => 'Assigned',
                        'unassigned' => 'Unassigned',
                    ])
                    ->visible(function (): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return (bool) $user?->hasRole('supervisor');
                    })
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        if (! $user?->hasRole('supervisor')) {
                            return $query;
                        }

                        return $query
                            ->when($value === 'assigned', fn ($q) => $q->whereNotNull('shift_assignments.id'))
                            ->when($value === 'unassigned', fn ($q) => $q->whereNull('shift_assignments.id'));
                    }),
                Tables\Filters\SelectFilter::make('shift_id')->relationship('shift', 'name')->label('Shift'),
                Tables\Filters\Filter::make('assigned_date')->form([
                    Forms\Components\TextInput::make('from_ec')
                        ->label('From (EC) YYYY-MM-DD')
                        ->mask('9999-99-99'),
                    Forms\Components\TextInput::make('until_ec')
                        ->label('Until (EC) YYYY-MM-DD')
                        ->mask('9999-99-99'),
                ])->query(function ($query, array $data) {
                    $from = isset($data['from_ec']) && $data['from_ec']
                        ? EthiopianDate::fromEcYmd($data['from_ec'])->toDateString()
                        : null;
                    $until = isset($data['until_ec']) && $data['until_ec']
                        ? EthiopianDate::fromEcYmd($data['until_ec'])->toDateString()
                        : null;

                    return $query
                        ->when($from, fn ($q, $v) => $q->whereDate('assigned_date', '>=', $v))
                        ->when($until, fn ($q, $v) => $q->whereDate('assigned_date', '<=', $v));
                }),
            ])
            ->modifyQueryUsing(function ($query) {
                /** @var \App\Models\User|null $user */
                $user = Auth::user();
                
                if ($user) {
                    if ($user->hasRole('supervisor')) {
                        return $query;
                    }
                    
                    // If user is an officer, show only their own assignments
                    if ($user->hasRole('officer')) {
                        $employee = Employee::where('user_id', $user->id)->first();
                        if ($employee) {
                            $query->where('employee_id', $employee->id);
                        }
                    }
                }
                
                return $query;
            })
            ->actions([
                Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->visible(function (ShiftAssignment $record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return (bool) $user
                            && $user->hasRole('supervisor')
                            && $user->can('assign_shifts')
                            && $record->status === 'unassigned';
                    })
                    ->url(fn (ShiftAssignment $record): string => static::getUrl('create', ['employee_id' => $record->employee_id])),
                Action::make('reshift')
                    ->label('Reshift')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->modalHeading('Reshift Officer')
                    ->modalDescription('Change shift details for this assigned officer.')
                    ->modalSubmitActionLabel('Save Changes')
                    ->visible(function (ShiftAssignment $record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return (bool) $user
                            && $user->hasRole('supervisor')
                            && $user->can('assign_shifts')
                            && $record->status === 'assigned'
                            && $record->id > 0;
                    })
                    ->form([
                        Forms\Components\Select::make('shift_id')
                            ->label('New Shift')
                            ->options(
                                Shift::query()
                                    ->where('is_active', true)
                                    ->orderBy('start_time')
                                    ->pluck('name', 'id')
                                    ->all()
                            )
                            ->required(),
                        Forms\Components\TextInput::make('zone')
                            ->required()
                            ->maxLength(120),
                    ])
                    ->action(function (ShiftAssignment $record, array $data): void {
                        $assignment = ShiftAssignment::find($record->id);
                        if (! $assignment) {
                            Notification::make()
                                ->title('Active assignment not found.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $assignment->update([
                            'shift_id' => $data['shift_id'],
                            'zone' => $data['zone'],
                            'assigned_by' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Officer re-shifted successfully.')
                            ->success()
                            ->send();
                    }),
                Action::make('check_in')
                    ->label(function (ShiftAssignment $record): string {
                        if ($record->status === 'unassigned' || $record->id <= 0) {
                            return 'Check in';
                        }

                        $attendance = Attendance::query()
                            ->where('employee_id', $record->employee_id)
                            ->where('shift_assignment_id', $record->id)
                            ->first();

                        if (! $attendance || ! $attendance->check_in) {
                            return 'Check in';
                        }

                        if (! $attendance->check_out) {
                            return 'Check out';
                        }

                        return 'Shift completed';
                    })
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (ShiftAssignment $record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        if (! $user || ! $user->can('manage_attendance')) {
                            return false;
                        }

                        if ($record->status === 'unassigned' || $record->id <= 0) {
                            return false;
                        }

                        // Button is only available during the shift window and while the shift is scheduled.
                        if (! $record->isWithinShift() || $record->status !== 'scheduled') {
                            return false;
                        }

                        // Hide once fully checked out.
                        $attendance = Attendance::query()
                            ->where('employee_id', $record->employee_id)
                            ->where('shift_assignment_id', $record->id)
                            ->first();

                        return ! ($attendance && $attendance->check_out);
                    })
                    ->action(function (ShiftAssignment $record): void {
                        if ($record->status === 'unassigned' || $record->id <= 0) {
                            Notification::make()
                                ->title('This officer has no active assignment.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $attendance = Attendance::firstOrNew([
                            'employee_id' => $record->employee_id,
                            'shift_assignment_id' => $record->id,
                        ]);

                        // Only allow actions during the shift window.
                        if (! $record->isWithinShift()) {
                            Notification::make()
                                ->title('You can check in or out only during the shift time.')
                                ->danger()
                                ->send();

                            return;
                        }

                        if (! $attendance->check_in) {
                            $attendance->check_in = now();

                            $attendance->save();

                            Notification::make()
                                ->title('Check-in recorded')
                                ->success()
                                ->send();

                            return;
                        }

                        if (! $attendance->check_out) {
                            $attendance->check_out = now();

                            $attendance->save();

                            Notification::make()
                                ->title('Check-out recorded')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Shift already completed for this assignment.')
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ShiftAssignmentResource\Pages\ListShiftAssignments::route('/'),
            'create' => \App\Filament\Resources\ShiftAssignmentResource\Pages\CreateShiftAssignment::route('/create'),
            'edit' => \App\Filament\Resources\ShiftAssignmentResource\Pages\EditShiftAssignment::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('view_shifts');
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Officers are read-only for shift assignments.
        if ($user->hasRole('officer')) {
            return false;
        }

        // Supervisors (and other roles) may create if they have the permission.
        return (bool) $user->can('assign_shifts');
    }

    public static function canEdit($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('assign_shifts');
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('assign_shifts');
    }
}