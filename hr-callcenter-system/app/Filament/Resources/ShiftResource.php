<?php

namespace App\Filament\Resources;

use App\Models\Employee;
use App\Models\Shift;
use App\Support\EthiopianTime;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Shift Management';

    protected static ?string $navigationLabel = 'Shift Types';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $cycleOptions = [
            EthiopianTime::CYCLE_DAY => EthiopianTime::cycleLabel(EthiopianTime::CYCLE_DAY),
            EthiopianTime::CYCLE_EVENING => EthiopianTime::cycleLabel(EthiopianTime::CYCLE_EVENING),
        ];

        $ethRules = [
            fn (): \Closure => function (string $attribute, $value, \Closure $fail): void {
                if ($value === null || $value === '') {
                    return;
                }
                try {
                    EthiopianTime::normalizeEthHm((string) $value);
                } catch (\Throwable $e) {
                    $fail($e->getMessage());
                }
            },
        ];

        return $schema->schema([
            Section::make(__('Shift type'))
                ->description(__('Times use the Ethiopian 12-hour clock (hours 1–12), with two periods: day (ከ 7:00 ሰዓት) and evening–dawn (ከ 7:00 ምሽት). Stored and interpreted only in this system; comparisons use Africa/Addis_Ababa.'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(120),
                    Forms\Components\TextInput::make('start_eth')
                        ->label(__('Start (Ethiopian clock)'))
                        ->placeholder('1:45')
                        ->helperText(__('Hour 1–12 and minutes, e.g. 7:00 for the seventh hour of the day period.'))
                        ->required()
                        ->rules($ethRules),
                    Forms\Components\Select::make('start_cycle')
                        ->label(__('Start period'))
                        ->options($cycleOptions)
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('end_eth')
                        ->label(__('End (Ethiopian clock)'))
                        ->placeholder('7:00')
                        ->required()
                        ->rules($ethRules),
                    Forms\Components\Select::make('end_cycle')
                        ->label(__('End period'))
                        ->options($cycleOptions)
                        ->required()
                        ->native(false),
                    Forms\Components\Textarea::make('description')
                        ->label(__('Description'))
                        ->maxLength(500)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')->label(__('Active'))->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_eth')
                    ->label(__('Start'))
                    ->formatStateUsing(fn ($state) => filled($state) ? (string) $state : '—'),
                Tables\Columns\TextColumn::make('end_eth')
                    ->label(__('End'))
                    ->formatStateUsing(fn ($state) => filled($state) ? (string) $state : '—'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('shift_assignments_count')
                    ->label('Assignments')
                    ->visible(function (): bool {
                        $user = auth()->user();

                        // Officers should not see assignment counts; supervisors/admins can.
                        return (bool) $user && ! $user->hasRole('officer');
                    })
                    ->sortable(),
            ])
            ->defaultSort('start_cycle')
            ->filters([])
            ->modifyQueryUsing(function ($query) {
                $query->orderBy('start_cycle')->orderBy('start_eth');

                $user = auth()->user();

                if (! $user) {
                    return $query->withCount('shiftAssignments');
                }

                // Supervisor: count only assignments they can manage in their woreda, for officers.
                if ($user->hasRole('supervisor')) {
                    $supervisor = Employee::where('user_id', $user->id)->first();

                    if ($supervisor && $supervisor->woreda_id) {
                        return $query->withCount(['shiftAssignments as shift_assignments_count' => function ($q) use ($supervisor) {
                            $q->whereHas('employee', function ($employeeQuery) use ($supervisor) {
                                $employeeQuery
                                    ->where('woreda_id', $supervisor->woreda_id)
                                    ->whereHas('user', fn ($u) => $u->role('officer'));
                            });
                        }]);
                    }

                    return $query->withCount('shiftAssignments');
                }

                // Officer: count only their own assignments.
                if ($user->hasRole('officer')) {
                    $employee = Employee::where('user_id', $user->id)->first();

                    if ($employee) {
                        return $query->withCount(['shiftAssignments as shift_assignments_count' => function ($q) use ($employee) {
                            $q->where('employee_id', $employee->id);
                        }]);
                    }

                    return $query->withCount('shiftAssignments');
                }

                // Admins / others: count all assignments.
                return $query->withCount('shiftAssignments');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ShiftResource\Pages\ListShifts::route('/'),
            'create' => \App\Filament\Resources\ShiftResource\Pages\CreateShift::route('/create'),
            'edit' => \App\Filament\Resources\ShiftResource\Pages\EditShift::route('/{record}/edit'),
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

        // Officers and supervisors are read-only for shift types.
        if ($user->hasRole('officer') || $user->hasRole('supervisor')) {
            return false;
        }

        return (bool) $user->can('manage_shifts');
    }

    public static function canEdit($record): bool
    {
        return (bool) auth()->user()?->can('manage_shifts');
    }

    public static function canDelete($record): bool
    {
        return (bool) auth()->user()?->can('manage_shifts');
    }
}
