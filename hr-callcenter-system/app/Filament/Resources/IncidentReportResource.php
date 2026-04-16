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
use Filament\Actions;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Exports\IncidentReportExporter;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncidentReportResource extends Resource
{
    protected static ?string $model = IncidentReport::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'am' ? 'ቅጣት እና እርምጃ' : 'Penalty & Action';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'am' ? 'የክስተት ሪፖርቶች' : 'Incident Reports';
    }

    public static function form(Schema $schema): Schema
    {
        $am = app()->getLocale() === 'am';

        return $schema->schema([
            Section::make($am ? 'የክስተት ሪፖርት' : 'Incident Reporting')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label($am ? 'ሰራተኛ' : 'Employee')
                        ->options(Employee::query()->orderBy('first_name_am')->get()->mapWithKeys(fn ($e) => [$e->id => $e->employee_id.' - '.$e->full_name_am])->all())
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('incident_type')
                        ->label($am ? 'የክስተት አይነት' : 'Incident Type')
                        ->options([
                            'misconduct' => $am ? 'ጥፋት' : 'Misconduct',
                            'non_compliance' => $am ? 'አለመከተል' : 'Non-compliance',
                            'violation' => $am ? 'ጥሰት' : 'Violation',
                            'attendance' => $am ? 'ቅጥ' : 'Attendance',
                            'harassment' => $am ? 'ትንኮሳ' : 'Harassment',
                            'corruption' => $am ? 'ሙስና' : 'Corruption',
                            'other' => $am ? 'ሌላ' : 'Other',
                        ])
                        ->required()
                        ->live(),
                    Forms\Components\TextInput::make('location')
                        ->label($am ? 'ቦታ' : 'Location')
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('incident_date')
                        ->label($am ? 'ቀን' : 'Date')
                        ->ethiopic()
                        ->firstDayOfWeek(1)
                        ->closeOnDateSelection()
                        ->displayFormat('Y-m-d')
                        ->default(now())
                        ->required(),
                    Forms\Components\Textarea::make('description')
                        ->label($am ? 'ዝርዝር' : 'Description')
                        ->required()
                        ->maxLength(8000)
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label($am ? 'ሁኔታ' : 'Status')
                                ->options([
                                    'reported' => $am ? 'ሪፖርት ተደርጓል' : 'Reported',
                                    'penalty_assigned' => $am ? 'ቅጣት ተመድቧል' : 'Penalty Assigned',
                                    'in_follow_up' => $am ? 'ክትትል ላይ' : 'In Follow-up',
                                    'closed' => $am ? 'ተዘግቷል' : 'Closed',
                                ])
                                ->default('reported')
                                ->required(),
                            Forms\Components\Select::make('reported_by')
                                ->label($am ? 'ያሳወቀው' : 'Reported By')
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
                    ->label('Paramilitary ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.full_name_am')
                    ->label('Paramilitary Name')
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
                    ->label(app()->getLocale() === 'am' ? 'ሁኔታ' : 'Status')
                    ->options([
                        'reported' => app()->getLocale() === 'am' ? 'ሪፖርት ተደርጓል' : 'Reported',
                        'penalty_assigned' => app()->getLocale() === 'am' ? 'ቅጣት ተመድቧል' : 'Penalty Assigned',
                        'in_follow_up' => app()->getLocale() === 'am' ? 'ክትትል ላይ' : 'In Follow-up',
                        'closed' => app()->getLocale() === 'am' ? 'ተዘግቷል' : 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('incident_type')
                    ->label(app()->getLocale() === 'am' ? 'የክስተት አይነት' : 'Incident Type')
                    ->options([
                        'misconduct' => app()->getLocale() === 'am' ? 'ጥፋት' : 'Misconduct',
                        'non_compliance' => app()->getLocale() === 'am' ? 'አለመከተል' : 'Non-compliance',
                        'violation' => app()->getLocale() === 'am' ? 'ጥሰት' : 'Violation',
                        'attendance' => app()->getLocale() === 'am' ? 'ቅጥ' : 'Attendance',
                        'harassment' => app()->getLocale() === 'am' ? 'ትንኮሳ' : 'Harassment',
                        'corruption' => app()->getLocale() === 'am' ? 'ሙስና' : 'Corruption',
                        'other' => app()->getLocale() === 'am' ? 'ሌላ' : 'Other',
                    ]),
            ])
            ->headerActions([
                Actions\ExportAction::make()
                    ->exporter(IncidentReportExporter::class)
                    ->formats([ExportFormat::Csv]),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\ExportBulkAction::make()
                    ->exporter(IncidentReportExporter::class)
                    ->formats([ExportFormat::Csv]),
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

        return (bool) $user && (
            $user->hasRole('admin')
            || $user->hasRole('supervisor')
            || $user->hasRole('officer')
            || $user->can('manage_penalty_action')
        );
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        // Officers report incidents, supervisors and admin can also create
        return (bool) $user && (
            $user->hasRole('admin')
            || $user->hasRole('supervisor')
            || $user->hasRole('officer')
            || $user->can('manage_penalty_action')
        );
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Admin and penalty officers can edit any
        if ($user->hasRole('admin') || $user->can('manage_penalty_action')) {
            return true;
        }

        // Supervisors can edit (they assign penalties and verify)
        if ($user->hasRole('supervisor')) {
            return true;
        }

        // Officers can only edit their own reports
        if ($user->hasRole('officer') && $record->reported_by === $user->id) {
            return true;
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        return (bool) $user && ($user->hasRole('admin') || $user->can('manage_penalty_action'));
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['employee', 'reportedBy']);

        if (! $user) {
            return $query;
        }

        if ($user->hasRole('admin') || $user->can('manage_penalty_action')) {
            return $query;
        }

        if ($user->hasRole('supervisor')) {
            return $query->whereHas('employee', function (Builder $q) use ($user) {
                if ($user->woreda) {
                    $q->where('woreda_id', $user->woreda);
                } elseif ($user->sub_city) {
                    $q->where('sub_city_id', $user->sub_city);
                }
            });
        }

        if ($user->hasRole('officer')) {
            return $query->where('reported_by', $user->id);
        }

        return $query;
    }
}
