<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncidentReportResource\Pages;
use App\Filament\Resources\IncidentReportResource\RelationManagers\FollowUpActionsRelationManager;
use App\Filament\Resources\IncidentReportResource\RelationManagers\PenaltyAssignmentsRelationManager;
use App\Models\Employee;
use App\Models\IncidentReport;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncidentReportResource extends Resource
{
    protected static ?string $model = IncidentReport::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Penalty & Action';

    protected static ?string $navigationLabel = 'Incident Reports';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Incident Reporting')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('Employee')
                        ->options(Employee::query()->orderBy('first_name_am')->get()->mapWithKeys(fn ($e) => [$e->id => $e->employee_id . ' - ' . $e->full_name_am])->all())
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('incident_type')
                        ->label('Incident Type')
                        ->options([
                            'misconduct' => 'Misconduct',
                            'non_compliance' => 'Non-compliance',
                            'violation' => 'Violation',
                            'attendance' => 'Attendance',
                            'harassment' => 'Harassment',
                            'corruption' => 'Corruption',
                            'other' => 'Other',
                        ])
                        ->required()
                        ->reactive(),
                    Forms\Components\TextInput::make('location')
                        ->label('Location')
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('incident_date')
                        ->label('Date')
                        ->default(now())
                        ->required(),
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->required()
                        ->maxLength(8000)
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'reported' => 'Reported',
                                    'penalty_assigned' => 'Penalty Assigned',
                                    'in_follow_up' => 'In Follow-up',
                                    'closed' => 'Closed',
                                ])
                                ->default('reported')
                                ->required(),
                            Forms\Components\Select::make('reported_by')
                                ->label('Reported By')
                                ->options(\App\Models\User::pluck('name', 'id'))
                                ->searchable()
                                ->default(auth()->id()),
                        ]),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.employee_id')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.full_name_am')
                    ->label('Employee Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('incident_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('incident_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reported' => 'secondary',
                        'penalty_assigned' => 'warning',
                        'in_follow_up' => 'info',
                        'closed' => 'success',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('reportedBy.name')
                    ->label('Reported By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('incident_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'reported' => 'Reported',
                        'penalty_assigned' => 'Penalty Assigned',
                        'in_follow_up' => 'In Follow-up',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PenaltyAssignmentsRelationManager::class,
            FollowUpActionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncidentReports::route('/'),
            'create' => Pages\CreateIncidentReport::route('/create'),
            'view' => Pages\ViewIncidentReport::route('/{record}'),
            'edit' => Pages\EditIncidentReport::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return (bool) $user && ($user->hasRole('admin') || $user->can('manage_penalty_action'));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['employee', 'reportedBy']);
    }
}

