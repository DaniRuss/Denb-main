<?php

namespace App\Filament\Resources;

use App\Models\DailyShiftReport;
use App\Models\Employee;
use App\Models\ShiftAssignment;
use App\Support\EthiopianDate;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ShiftReportResource extends Resource
{
    protected static ?string $model = DailyShiftReport::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Shift Management';

    protected static ?string $navigationLabel = 'Daily Reports';

    protected static ?int $navigationSort = 6;

    public static function getEloquentQuery(): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        if (! $user) {
            return $query;
        }

        // Officers should only ever see their own daily reports.
        if ($user->hasRole('officer')) {
            $employee = Employee::query()->where('user_id', $user->id)->first();

            return $employee
                ? $query->where('employee_id', $employee->id)
                : $query->whereRaw('1 = 0');
        }

        // Supervisors should only ever see daily reports within their sub_city/woreda,
        // for officer employees only.
        if ($user->hasRole('supervisor')) {
            $supervisor = Employee::query()->where('user_id', $user->id)->first();

            if (! $supervisor) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('employee', function (Builder $employeeQuery) use ($supervisor) {
                $employeeQuery->whereHas('user', fn ($q) => $q->role('officer'));
                $employeeQuery->where('id', '!=', $supervisor->id);

                if ($supervisor->sub_city_id) {
                    $employeeQuery->where('sub_city_id', $supervisor->sub_city_id);
                }

                if ($supervisor->woreda_id) {
                    $employeeQuery->where('woreda_id', $supervisor->woreda_id);
                }
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Daily Shift Report')
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
                                ->mapWithKeys(fn ($e) => [$e->id => $e->employee_id . ' – ' . $e->full_name_am])
                                ->all();
                        })
                        ->searchable()
                        ->required()
                        ->live(),
                    Forms\Components\Select::make('shift_assignment_id')
                        ->label('Shift Assignment')
                        ->options(function (Get $get) {
                            $employeeId = $get('employee_id');
                            if (! $employeeId) {
                                return [];
                            }
                            return ShiftAssignment::query()
                                ->where('employee_id', $employeeId)
                                ->whereIn('status', ['scheduled', 'completed'])
                                ->with('shift')
                                ->orderBy('assigned_date', 'desc')
                                ->get()
                                ->mapWithKeys(fn ($a) => [$a->id => (EthiopianDate::toEcYmd($a->assigned_date) ?? $a->assigned_date->format('Y-m-d')) . ' – ' . ($a->shift?->name ?? '') . ' (Zone ' . $a->zone . ')'])
                                ->all();
                        })
                        ->required()
                        ->live(),
                    Forms\Components\Textarea::make('report_text')
                        ->label('Report')
                        ->maxLength(10000)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('incident_count')
                        ->numeric()
                        ->minValue(0)
                        ->default(0),
                    Forms\Components\TextInput::make('penalty_count')
                        ->numeric()
                        ->minValue(0)
                        ->default(0),
                    Forms\Components\DateTimePicker::make('submitted_at')
                        ->default(now())
                        ->required()
                        ->seconds(false),
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
                    ->label('Shift date (EC)')
                    ->formatStateUsing(fn ($state) => EthiopianDate::toEcYmd($state) ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shiftAssignment.shift.name')->label('Shift'),
                Tables\Columns\TextColumn::make('incident_count')->sortable(),
                Tables\Columns\TextColumn::make('penalty_count')->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submitted (EC)')
                    ->formatStateUsing(fn ($state) => EthiopianDate::toEcYmdHi($state) ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_text')->limit(50)->wrap()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5)
            ->modifyQueryUsing(function ($query) {
                /** @var \App\Models\User|null $user */
                $user = Auth::user();
                if ($user && $user->hasRole('officer')) {
                    $employee = Employee::query()->where('user_id', $user->id)->first();
                    if ($employee) {
                        $query->where('employee_id', $employee->id);
                    }
                }

                if ($user && $user->hasRole('supervisor')) {
                    $supervisor = Employee::query()->where('user_id', $user->id)->first();

                    if ($supervisor) {
                        $query->whereHas('employee', function (Builder $employeeQuery) use ($supervisor) {
                            $employeeQuery->whereHas('user', fn ($q) => $q->role('officer'));
                            $employeeQuery->where('id', '!=', $supervisor->id);

                            if ($supervisor->sub_city_id) {
                                $employeeQuery->where('sub_city_id', $supervisor->sub_city_id);
                            }

                            if ($supervisor->woreda_id) {
                                $employeeQuery->where('woreda_id', $supervisor->woreda_id);
                            }
                        });
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                }
                return $query;
            })
            ->filters([])
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ShiftReportResource\Pages\ListShiftReports::route('/'),
            'create' => \App\Filament\Resources\ShiftReportResource\Pages\CreateShiftReport::route('/create'),
            'view' => \App\Filament\Resources\ShiftReportResource\Pages\ViewShiftReport::route('/{record}'),
            'edit' => \App\Filament\Resources\ShiftReportResource\Pages\EditShiftReport::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return (bool) $user && ($user->can('view_shift_reports') || $user->can('submit_shift_report'));
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('submit_shift_report');
    }

    public static function canEdit($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('submit_shift_report');
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('view_shift_reports');
    }
}
