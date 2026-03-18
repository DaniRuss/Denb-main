<?php

namespace App\Filament\Resources;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\ShiftAssignment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ShiftAssignmentResource extends Resource
{
    protected static ?string $model = ShiftAssignment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Shift Management';

    protected static ?string $navigationLabel = 'Shift Assignment';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Assignment')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('Employee')
                        ->options(function () {
                            $user = auth()->user();
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

                            // If user is a supervisor, filter employees by their woreda_id
                            if ($user && $user->hasRole('supervisor')) {
                                $supervisor = Employee::where('user_id', $user->id)->first();

                                if ($supervisor && $supervisor->woreda_id) {
                                    $query
                                        ->where('woreda_id', $supervisor->woreda_id)
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
                    Forms\Components\DatePicker::make('assigned_date')
                        ->label('Start date')
                        ->required()
                        ->default(now())
                        ->minDate(now())
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (! $state) {
                                return;
                            }

                            $start = \Illuminate\Support\Carbon::parse($state)->startOfDay();
                            // exactly 30 days: start + 29
                            $set('end_date', $start->copy()->addDays(29));
                        })
                        ->rule(function (Get $get, ?ShiftAssignment $record): \Closure {
                            return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                $employeeId = $get('employee_id');
                                if (! $employeeId || ! $value) {
                                    return;
                                }

                                $date = \Illuminate\Support\Carbon::parse($value)->toDateString();

                                // Prevent duplicate assignment starting this date for same employee
                                $exists = ShiftAssignment::query()
                                    ->where('employee_id', $employeeId)
                                    ->whereDate('assigned_date', $date)
                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    ->exists();

                                if ($exists) {
                                    $fail(__('This employee already has a shift assigned starting on this date.'));
                                }
                            };
                        }),
                    Forms\Components\DatePicker::make('end_date')
                        ->label('End date')
                        ->disabled()
                        ->dehydrated()
                        ->required(),
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
                Tables\Columns\TextColumn::make('employee.employee_id')->label('Employee ID')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('employee.full_name_am')->label('Employee')->searchable(['first_name_am', 'last_name_am']),
                Tables\Columns\TextColumn::make('shift.name')->sortable(),
                Tables\Columns\TextColumn::make('zone')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('assigned_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'gray',
                        'no_show' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('assignedBy.name')->label('Assigned by')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('assigned_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('shift_id')->relationship('shift', 'name')->label('Shift'),
                Tables\Filters\Filter::make('assigned_date')->form([
                    Forms\Components\DatePicker::make('from')->label('From'),
                    Forms\Components\DatePicker::make('until')->label('Until'),
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data['from'], fn ($q, $v) => $q->whereDate('assigned_date', '>=', $v))
                        ->when($data['until'], fn ($q, $v) => $q->whereDate('assigned_date', '<=', $v));
                }),
            ])
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();
                
                if ($user) {
                    // If user is a supervisor, filter by their woreda_id
                    if ($user->hasRole('supervisor')) {
                        $supervisor = Employee::where('user_id', $user->id)->first();
                        if ($supervisor && $supervisor->woreda_id) {
                            $query->whereHas('employee', function ($q) use ($supervisor) {
                                $q->where('woreda_id', $supervisor->woreda_id);
                            });
                        }
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
                Action::make('check_in')
                    ->label('Check in')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ShiftAssignment $record): bool => auth()->user()?->can('manage_attendance') && $record->status === 'scheduled')
                    ->action(function (ShiftAssignment $record): void {
                        $attendance = Attendance::firstOrNew([
                            'employee_id' => $record->employee_id,
                            'shift_assignment_id' => $record->id,
                        ]);

                        // Only set check-in if not already set.
                        if (! $attendance->check_in) {
                            $attendance->check_in = now();
                        }

                        $attendance->save();

                        Notification::make()
                            ->title('Check-in recorded')
                            ->success()
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
        return (bool) auth()->user()?->can('view_shifts');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

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
        return (bool) auth()->user()?->can('assign_shifts');
    }

    public static function canDelete($record): bool
    {
        return (bool) auth()->user()?->can('assign_shifts');
    }
}