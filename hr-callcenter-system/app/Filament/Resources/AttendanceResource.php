<?php

namespace App\Filament\Resources;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\ShiftAssignment;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Shift Management';

    protected static ?string $navigationLabel = 'Attendance';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $employee = $user instanceof User
            ? Employee::query()->where('user_id', $user->id)->first()
            : null;
        $defaultEmployeeId = $employee?->id;

        return $schema->schema([
            Section::make('Attendance')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('Employee')
                        ->options(
                            Employee::query()
                                ->active()
                                ->orderBy('first_name_am')
                                ->get()
                                ->mapWithKeys(fn ($e) => [$e->id => $e->employee_id . ' – ' . $e->full_name_am])
                                ->all()
                        )
                        ->searchable()
                        ->default($defaultEmployeeId)
                        ->required()
                        ->live()
                        ->disabled(fn () => auth()->user()?->hasRole('officer') && $defaultEmployeeId),
                    Forms\Components\Select::make('shift_assignment_id')
                        ->label('Shift Assignment')
                        ->options(fn (Get $get) => ShiftAssignment::query()
                            ->when($get('employee_id'), fn ($q, $v) => $q->where('employee_id', $v))
                            ->whereIn('status', ['scheduled', 'completed'])
                            ->orderBy('assigned_date', 'desc')
                            ->get()
                            ->mapWithKeys(fn ($a) => [$a->id => $a->assigned_date->format('Y-m-d') . ' – ' . $a->shift?->name . ' (Zone ' . $a->zone . ')'])
                            ->all())
                        ->searchable()
                        ->required()
                        ->disabled(fn () => auth()->user()?->hasRole('officer') && $defaultEmployeeId),
                    Forms\Components\DateTimePicker::make('check_in')
                        ->seconds(false)
                        ->default(now())
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\DateTimePicker::make('check_out')
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
                Tables\Columns\TextColumn::make('shiftAssignment.assigned_date')->date()->label('Shift date')->sortable(),
                Tables\Columns\TextColumn::make('shiftAssignment.shift.name')->label('Shift'),
                Tables\Columns\TextColumn::make('check_in')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('check_out')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('attendance_status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'late' => 'warning',
                        'pending' => 'gray',
                        'half_day' => 'info',
                        default => 'gray',
                    }),
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
            ])
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();
                if ($user && $user->hasRole('officer')) {
                    $employee = Employee::query()->where('user_id', $user->id)->first();
                    if ($employee) {
                        $query->where('employee_id', $employee->id);
                    }
                }
                return $query;
            })
            ->actions([]);
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
        $user = auth()->user();
        return (bool) $user && ($user->can('view_attendance') || $user->can('manage_attendance'));
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('manage_attendance');
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

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
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($record instanceof Attendance && $record->status_locked) {
            return $user->can('override_attendance_lock');
        }

        return $user->can('manage_attendance');
    }
}
