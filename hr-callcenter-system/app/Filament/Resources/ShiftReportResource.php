<?php

namespace App\Filament\Resources;

use App\Models\DailyShiftReport;
use App\Models\Employee;
use App\Models\ShiftAssignment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftReportResource extends Resource
{
    protected static ?string $model = DailyShiftReport::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Shift Management';

    protected static ?string $navigationLabel = 'Daily Reports';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Daily Shift Report')
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
                                ->mapWithKeys(fn ($a) => [$a->id => $a->assigned_date->format('Y-m-d') . ' – ' . ($a->shift?->name ?? '') . ' (Zone ' . $a->zone . ')'])
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
                Tables\Columns\TextColumn::make('shiftAssignment.assigned_date')->date()->label('Shift date')->sortable(),
                Tables\Columns\TextColumn::make('shiftAssignment.shift.name')->label('Shift'),
                Tables\Columns\TextColumn::make('incident_count')->sortable(),
                Tables\Columns\TextColumn::make('penalty_count')->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('report_text')->limit(50)->wrap()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5)
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
        $user = auth()->user();
        return (bool) $user && ($user->can('view_shift_reports') || $user->can('submit_shift_report'));
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('submit_shift_report');
    }

    public static function canEdit($record): bool
    {
        return (bool) auth()->user()?->can('submit_shift_report');
    }

    public static function canDelete($record): bool
    {
        return (bool) auth()->user()?->can('view_shift_reports');
    }
}
