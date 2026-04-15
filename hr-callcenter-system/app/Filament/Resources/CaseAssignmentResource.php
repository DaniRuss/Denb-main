<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaseAssignments\Pages;
use App\Models\CaseAssignment;
use App\Models\Complaint;
use App\Models\Officer;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CaseAssignmentResource extends Resource
{
    protected static ?string $model = CaseAssignment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Case Management';

    protected static ?string $navigationLabel = 'Case Assignments';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        $am = app()->getLocale() === 'am';

        return $schema->schema([
            \Filament\Schemas\Components\Section::make($am ? 'የምደባ ዝርዝር' : 'Assignment Details')
                ->schema([
                    \Filament\Forms\Components\Select::make('complaint_id')
                        ->label($am ? 'ቅሬታ' : 'Complaint')
                        ->options(Complaint::pluck('ticket_number', 'id'))
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('officer_id')
                        ->label($am ? 'ኦፊሰር' : 'Assign to Officer')
                        ->options(Officer::with('user')->get()->pluck('user.name', 'id'))
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('assigned_by')
                        ->label($am ? 'የመደበው' : 'Assigned By')
                        ->options(User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'supervisor']))->pluck('name', 'id'))
                        ->searchable()
                        ->default(auth()->id()),

                    \Filament\Forms\Components\Select::make('status')
                        ->label($am ? 'ሁኔታ' : 'Status')
                        ->options([
                            'assigned' => $am ? 'ተመድቧል' : 'Assigned',
                            'in_progress' => $am ? 'በሂደት ላይ' : 'In Progress',
                            'completed' => $am ? 'ተጠናቋል' : 'Completed',
                            'reassigned' => $am ? 'እንደገና ተመድቧል' : 'Reassigned',
                        ])
                        ->default('assigned')
                        ->required(),

                    \Filament\Forms\Components\DatePicker::make('due_date')
                        ->label($am ? 'የገደብ ቀን' : 'Due Date')
                        ->ethiopic()
                        ->firstDayOfWeek(1)
                        ->closeOnDateSelection(),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label($am ? 'ማስታወሻ' : 'Assignment Notes')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        $am = app()->getLocale() === 'am';

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('complaint.ticket_number')
                    ->label($am ? 'ቲኬት ቁ.' : 'Ticket #')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('complaint.complaint_type')
                    ->label($am ? 'የቅሬታ አይነት' : 'Complaint Type')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state ?? ''))),

                Tables\Columns\TextColumn::make('officer.user.name')
                    ->label($am ? 'ኦፊሰር' : 'Assigned Officer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->label($am ? 'የመደበው' : 'Assigned By'),

                Tables\Columns\TextColumn::make('status')
                    ->label($am ? 'ሁኔታ' : 'Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'assigned' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'reassigned' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('due_date')
                    ->label($am ? 'የገደብ ቀን' : 'Due Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label($am ? 'የተመደበበት' : 'Assigned On')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label($am ? 'ሁኔታ' : 'Status')
                    ->options([
                        'assigned' => $am ? 'ተመድቧል' : 'Assigned',
                        'in_progress' => $am ? 'በሂደት ላይ' : 'In Progress',
                        'completed' => $am ? 'ተጠናቋል' : 'Completed',
                        'reassigned' => $am ? 'እንደገና ተመድቧል' : 'Reassigned',
                    ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCaseAssignments::route('/'),
            'create' => Pages\CreateCaseAssignment::route('/create'),
            'view' => Pages\ViewCaseAssignment::route('/{record}'),
            'edit' => Pages\EditCaseAssignment::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('admin')
            || $user->hasRole('supervisor')
            || $user->can('assign_cases')
        );
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('admin')
            || $user->hasRole('supervisor')
            || $user->can('assign_cases')
        );
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('admin')
            || $user->hasRole('supervisor')
            || $user->can('assign_cases')
        );
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        return (bool) $user && ($user->hasRole('admin') || $user->can('manage_penalty_action'));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['complaint', 'officer.user', 'assignedBy']);

        if (! $user) {
            return $query;
        }

        if ($user->hasRole('admin') || $user->can('manage_penalty_action')) {
            return $query;
        }

        if ($user->hasRole('supervisor')) {
            return $query->where('assigned_by', $user->id);
        }

        return $query;
    }
}
