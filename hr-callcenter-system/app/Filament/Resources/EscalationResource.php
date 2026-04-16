<?php

namespace App\Filament\Resources;

use App\Models\Escalation;
use App\Models\Complaint;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Escalations\Pages;
use Illuminate\Database\Eloquent\Builder;

class EscalationResource extends Resource
{
    protected static ?string $model = Escalation::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static string|\UnitEnum|null $navigationGroup = 'Case Management';
    protected static ?string $navigationLabel = 'Escalations';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        $am = app()->getLocale() === 'am';

        return $schema->schema([
            \Filament\Schemas\Components\Section::make($am ? 'የእርከን ዝርዝር' : 'Escalation Details')
                ->schema([
                    \Filament\Forms\Components\Select::make('complaint_id')
                        ->label($am ? 'ቅሬታ' : 'Complaint')
                        ->options(Complaint::pluck('ticket_number', 'id'))
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('escalated_by')
                        ->label($am ? 'ያሳደገው' : 'Escalated By')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->default(auth()->id()),

                    \Filament\Forms\Components\Select::make('escalated_to')
                        ->label($am ? 'የተላለፈለት' : 'Escalated To')
                        ->options(User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'supervisor', 'director']))->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    \Filament\Forms\Components\Select::make('level')
                        ->label($am ? 'የእርከን ደረጃ' : 'Escalation Level')
                        ->options([
                            '1' => $am ? 'ደረጃ 1 — ቡድን መሪ' : 'Level 1 — Team Lead',
                            '2' => $am ? 'ደረጃ 2 — ተቆጣጣሪ' : 'Level 2 — Supervisor',
                            '3' => $am ? 'ደረጃ 3 — ዳይሬክተር' : 'Level 3 — Director',
                            '4' => $am ? 'ደረጃ 4 — ኮሚሽነር' : 'Level 4 — Commissioner',
                        ])
                        ->required(),

                    \Filament\Forms\Components\Select::make('status')
                        ->label($am ? 'ሁኔታ' : 'Status')
                        ->options([
                            'open' => $am ? 'ክፍት' : 'Open',
                            'in_review' => $am ? 'በግምገማ ላይ' : 'In Review',
                            'resolved' => $am ? 'ተፈትቷል' : 'Resolved',
                            'closed' => $am ? 'ተዘግቷል' : 'Closed',
                        ])
                        ->default('open')
                        ->required(),

                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label($am ? 'ምክንያት' : 'Reason for Escalation')
                        ->required()
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label($am ? 'ማስታወሻ' : 'Additional Notes')
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

                Tables\Columns\TextColumn::make('escalatedBy.name')
                    ->label($am ? 'ያሳደገው' : 'Escalated By'),

                Tables\Columns\TextColumn::make('escalatedTo.name')
                    ->label($am ? 'የተላለፈለት' : 'Escalated To'),

                Tables\Columns\TextColumn::make('level')
                    ->label($am ? 'ደረጃ' : 'Level')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ($am ? 'ደረጃ ' : 'Level ') . $state),

                Tables\Columns\TextColumn::make('status')
                    ->label($am ? 'ሁኔታ' : 'Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'open' => 'danger',
                        'in_review' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label($am ? 'ቀን' : 'Escalated On')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label($am ? 'ሁኔታ' : 'Status')
                    ->options([
                        'open' => $am ? 'ክፍት' : 'Open',
                        'in_review' => $am ? 'በግምገማ ላይ' : 'In Review',
                        'resolved' => $am ? 'ተፈትቷል' : 'Resolved',
                        'closed' => $am ? 'ተዘግቷል' : 'Closed',
                    ]),
                SelectFilter::make('level')
                    ->label($am ? 'ደረጃ' : 'Level')
                    ->options([
                        '1' => $am ? 'ደረጃ 1' : 'Level 1',
                        '2' => $am ? 'ደረጃ 2' : 'Level 2',
                        '3' => $am ? 'ደረጃ 3' : 'Level 3',
                        '4' => $am ? 'ደረጃ 4' : 'Level 4',
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
            'index' => Pages\ListEscalations::route('/'),
            'create' => Pages\CreateEscalation::route('/create'),
            'view' => Pages\ViewEscalation::route('/{record}'),
            'edit' => Pages\EditEscalation::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('admin')
            || $user->hasRole('supervisor')
            || $user->can('escalate_to_court')
            || $user->can('escalate_to_task_force')
        );
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('admin')
            || $user->hasRole('supervisor')
            || $user->can('escalate_to_court')
            || $user->can('escalate_to_task_force')
        );
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin') || $user->can('manage_penalty_action')) {
            return true;
        }

        if ($user->hasRole('supervisor')) {
            return true;
        }

        if ($record->escalated_by === $user->id) {
            return true;
        }

        return false;
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
        $query = parent::getEloquentQuery()->with(['complaint', 'escalatedBy', 'escalatedTo']);

        if (! $user) {
            return $query;
        }

        if ($user->hasRole('admin') || $user->can('manage_penalty_action')) {
            return $query;
        }

        if ($user->hasRole('supervisor')) {
            return $query->where(function (Builder $q) use ($user) {
                $q->where('escalated_by', $user->id)
                    ->orWhere('escalated_to', $user->id);
            });
        }

        return $query->where('escalated_by', $user->id);
    }
}
