<?php

namespace App\Filament\Resources;

use App\Models\Shift;
use App\Models\Employee;
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
        return $schema->schema([
            Section::make('Shift Type')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\TimePicker::make('start_time')
                        ->required()
                        ->seconds(false)
                        ->format('H:i'),
                    Forms\Components\TimePicker::make('end_time')
                        ->required()
                        ->seconds(false)
                        ->format('H:i'),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(500)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_time')->time('H:i')->sortable(),
                Tables\Columns\TextColumn::make('end_time')->time('H:i')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('shift_assignments_count')
                    ->label('Assignments')
                    ->sortable(),
            ])
            ->defaultSort('start_time')
            ->filters([])
            ->modifyQueryUsing(function ($query) {
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
