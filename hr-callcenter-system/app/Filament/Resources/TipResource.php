<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipResource\Pages;
use App\Models\Tip;
use App\Models\User;
use App\Services\TipWorkflowService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TipResource extends Resource
{
    protected static ?string $model = Tip::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-phone-arrow-up-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Case Management';

    protected static ?string $navigationLabel = 'Tips';

    protected static ?string $pluralLabel = 'Tips';

    protected static ?string $modelLabel = 'Tip';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Record New Tip')
                ->schema([
                    Forms\Components\Select::make('tip_type')
                        ->label('Type of Illegal Activity')
                        ->options(Tip::getTipTypeOptions())
                        ->required()
                        ->reactive(),
                    Forms\Components\TextInput::make('tip_type_other')
                        ->label('Please Specify Type')
                        ->maxLength(255)
                        ->visible(fn ($get) => $get('tip_type') === 'other')
                        ->required(fn ($get) => $get('tip_type') === 'other'),
                    Forms\Components\Select::make('urgency_level')
                        ->label('Urgency Level')
                        ->options(Tip::getUrgencyOptions())
                        ->required()
                        ->default('medium'),
                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->rows(6)
                        ->maxLength(5000)
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('caller_name')
                                ->label('Caller Name')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('caller_phone')
                                ->label('Caller Phone')
                                ->maxLength(30),
                        ]),
                    Forms\Components\TextInput::make('unique_place')
                        ->label('Unique Place (ልዩ መጠርያ)')
                        ->maxLength(255),
                    Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('sub_city')
                                ->options(Tip::getAddisAbabaSubCities())
                                ->required()
                                ->searchable(),
                            Forms\Components\Select::make('woreda')
                                ->options(Tip::getWoredaOptions())
                                ->required(),
                            Forms\Components\Placeholder::make('date_time')
                                ->label('Date & Time')
                                ->content(now()->format('Y-m-d H:i:s')),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordClasses(fn (Tip $record) => match ($record->status) {
                Tip::STATUS_PENDING, Tip::STATUS_SUBMITTED => 'bg-gray-50/50 dark:bg-gray-900/50',
                Tip::STATUS_DISPATCHED => 'bg-green-50/50 dark:bg-green-900/50',
                Tip::STATUS_UNDER_INVESTIGATION, Tip::STATUS_INVESTIGATING => 'bg-yellow-50/50 dark:bg-yellow-900/50',
                Tip::STATUS_CLOSED => 'bg-blue-50/50 dark:bg-blue-900/50',
                Tip::STATUS_REJECTED, Tip::STATUS_FALSE_REPORT => 'bg-red-50/50 dark:bg-red-900/50',
                default => null,
            })
            ->columns([
                Tables\Columns\TextColumn::make('tip_number')
                    ->label('Tip #')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tip_source')
                    ->label('Source')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === Tip::SOURCE_CALL_CENTER ? 'Call Center' : 'Public'),
                Tables\Columns\TextColumn::make('tip_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? (Tip::getTipTypeOptions()[$state] ?? $state) : '-'),
                Tables\Columns\TextColumn::make('urgency_level')
                    ->label('Urgency')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'low' => 'secondary',
                        'medium' => 'info',
                        'high' => 'warning',
                        'immediate' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? ucfirst($state) : '-'),
                Tables\Columns\TextColumn::make('caller_name')
                    ->label('Caller')
                    ->searchable()
                    ->placeholder('Not provided'),
                Tables\Columns\TextColumn::make('sub_city')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('woreda')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Woreda ' . $state : '-'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => Tip::getStatusColors()[$state] ?? 'secondary')
                    ->formatStateUsing(fn (string $state): string => Tip::getStatusLabels()[$state] ?? str($state)->replace('_', ' ')->title()->toString()),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Recorded By')
                    ->default('Portal/Public'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tip_source')
                    ->options([
                        Tip::SOURCE_CALL_CENTER => 'Call Center',
                        Tip::SOURCE_PUBLIC => 'Public',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options(Tip::getStatusLabels()),
                Tables\Filters\SelectFilter::make('sub_city')
                    ->options(Tip::getAddisAbabaSubCities()),
                Tables\Filters\Filter::make('my_queue')
                    ->label('My Queue')
                    ->query(function (Builder $query): Builder {
                        $user = auth()->user();

                        if (! $user) {
                            return $query;
                        }

                        if ($user->can('view_own_call_tips')) {
                            return $query->where('tip_source', Tip::SOURCE_CALL_CENTER)->where('created_by', $user->id);
                        }

                        if ($user->can('review_supervisor_call_tips')) {
                            return $query->where('tip_source', Tip::SOURCE_CALL_CENTER)->where('status', Tip::STATUS_PENDING_SUPERVISOR_REVIEW);
                        }

                        if ($user->can('review_director_call_tips')) {
                            return $query->where('tip_source', Tip::SOURCE_CALL_CENTER)->where('status', Tip::STATUS_PENDING_DIRECTOR_REVIEW);
                        }

                        if ($user->can('manage_sub_city_call_tips') && filled($user->sub_city)) {
                            return $query
                                ->where('tip_source', Tip::SOURCE_CALL_CENTER)
                                ->where('sub_city', $user->sub_city);
                        }

                        return $query;
                    })
                    ->toggle(),
            ])
            ->actions([
                ViewAction::make()
                    ->label('View'),

                Action::make('supervisor_approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->form([
                        Forms\Components\Textarea::make('comment')
                            ->label('Supervisor Comment')
                            ->maxLength(2000),
                    ])
                    ->action(function (Tip $record, array $data): void {
                        app(TipWorkflowService::class)->reviewBySupervisor($record, 'approve', $data['comment'] ?? null);

                        Notification::make()->title('Tip forwarded to director review')->success()->send();
                    })
                    ->visible(fn (Tip $record): bool => static::canRunSupervisorAction($record)),

                Action::make('supervisor_reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->form([
                        Forms\Components\Textarea::make('comment')
                            ->label('Supervisor Comment')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (Tip $record, array $data): void {
                        app(TipWorkflowService::class)->reviewBySupervisor($record, 'reject', $data['comment']);

                        Notification::make()->title('Tip rejected by supervisor')->danger()->send();
                    })
                    ->visible(fn (Tip $record): bool => static::canRunSupervisorAction($record)),

                Action::make('director_approve')
                    ->label('Dispatch')
                    ->color('success')
                    ->icon('heroicon-o-paper-airplane')
                    ->form([
                        Forms\Components\Radio::make('dispatch_to')
                            ->label('Dispatch To')
                            ->options(function (Tip $record) {
                                return [
                                    'sub_city' => "Sub City Office ({$record->sub_city})",
                                    'woreda' => "Woreda Office (Woreda {$record->woreda})",
                                ];
                            })
                            ->default('sub_city')
                            ->required(),
                        Forms\Components\Textarea::make('comment')
                            ->label('Director Comment')
                            ->maxLength(2000),
                    ])
                    ->action(function (Tip $record, array $data): void {
                        app(TipWorkflowService::class)->reviewByDirector($record, 'approve', $data['comment'] ?? null, $data['dispatch_to']);

                        $target = $data['dispatch_to'] === 'sub_city' ? "the sub-city office" : "Woreda {$record->woreda} office";
                        Notification::make()->title("Tip dispatched to {$target}")->success()->send();
                    })
                    ->visible(fn (Tip $record): bool => static::canRunDirectorAction($record)),

                Action::make('director_reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-no-symbol')
                    ->form([
                        Forms\Components\Textarea::make('comment')
                            ->label('Director Comment')
                            ->required()
                            ->maxLength(2000),
                    ])
                    ->action(function (Tip $record, array $data): void {
                        app(TipWorkflowService::class)->reviewByDirector($record, 'reject', $data['comment']);

                        Notification::make()->title('Tip rejected by director')->danger()->send();
                    })
                    ->visible(fn (Tip $record): bool => static::canRunDirectorAction($record)),

                Action::make('update_investigation')
                    ->label('Update Investigation')
                    ->color('warning')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->form([
                        Forms\Components\Select::make('investigation_status')
                            ->options([
                                Tip::STATUS_UNDER_INVESTIGATION => 'Under Investigation',
                                Tip::STATUS_CLOSED => 'Closed',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('sub_city_notes')
                            ->label('Notes')
                            ->maxLength(4000),
                    ])
                    ->action(function (Tip $record, array $data): void {
                        app(TipWorkflowService::class)->updateInvestigation($record, $data);

                        Notification::make()->title('Investigation status updated')->success()->send();
                    })
                    ->visible(fn (Tip $record): bool => static::canRunSubCityAction($record)),

                Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->options(User::pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (Tip $record, array $data): void {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'status' => Tip::STATUS_INVESTIGATING,
                        ]);

                        Notification::make()->title('Public tip assigned')->success()->send();
                    })
                    ->visible(fn (Tip $record): bool => $record->tip_source !== Tip::SOURCE_CALL_CENTER && $record->status === Tip::STATUS_PENDING && static::isAdmin()),

                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(function (Tip $record): void {
                        $record->update(['status' => Tip::STATUS_VERIFIED]);

                        Notification::make()->title('Public tip verified')->success()->send();
                    })
                    ->visible(fn (Tip $record): bool => $record->tip_source !== Tip::SOURCE_CALL_CENTER && $record->status === Tip::STATUS_INVESTIGATING && static::isAdmin()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => static::isAdmin()),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make('Tip Summary')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(3)
                        ->schema([
                            \Filament\Infolists\Components\TextEntry::make('tip_number')->label('Tip Number'),
                            \Filament\Infolists\Components\TextEntry::make('tip_source')
                                ->label('Source')
                                ->formatStateUsing(fn (string $state): string => $state === Tip::SOURCE_CALL_CENTER ? 'Call Center' : 'Public'),
                            \Filament\Infolists\Components\TextEntry::make('status')
                                ->badge()
                                ->formatStateUsing(fn (string $state): string => Tip::getStatusLabels()[$state] ?? str($state)->replace('_', ' ')->title()->toString()),
                        ]),
                    \Filament\Infolists\Components\TextEntry::make('tip_type')
                        ->label('Type')
                        ->formatStateUsing(fn (?string $state): string => filled($state) ? (Tip::getTipTypeOptions()[$state] ?? $state) : '-'),
                    \Filament\Infolists\Components\TextEntry::make('urgency_level')
                        ->label('Urgency')
                        ->formatStateUsing(fn (?string $state): string => filled($state) ? ucfirst($state) : '-'),
                    \Filament\Infolists\Components\TextEntry::make('description')
                        ->markdown()
                        ->columnSpanFull(),
                ]),
            \Filament\Schemas\Components\Section::make('Caller & Location')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(2)
                        ->schema([
                            \Filament\Infolists\Components\TextEntry::make('caller_name')->label('Caller Name')->placeholder('Not provided'),
                            \Filament\Infolists\Components\TextEntry::make('caller_phone')->label('Caller Phone')->placeholder('Not provided'),
                            \Filament\Infolists\Components\TextEntry::make('sub_city')->label('Sub City'),
                            \Filament\Infolists\Components\TextEntry::make('woreda')
                                ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Woreda ' . $state : '-'),
                            \Filament\Infolists\Components\TextEntry::make('createdBy.name')->label('Recorded By')->placeholder('Portal/Public'),
                            \Filament\Infolists\Components\TextEntry::make('created_at')->label('Created At')->dateTime(),
                        ]),
                ]),
            \Filament\Schemas\Components\Section::make('Location Details')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('sub_city')->label('Sub City'),
                    \Filament\Infolists\Components\TextEntry::make('woreda')
                        ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Woreda ' . $state : '-'),
                    \Filament\Infolists\Components\TextEntry::make('unique_place')->label('Unique Place (ልዩ መጠርያ)'),
                    \Filament\Infolists\Components\TextEntry::make('specific_address')->label('Specific Address'),
                ]),
            \Filament\Schemas\Components\Section::make('Workflow Notes')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('supervisor_comment')->label('Supervisor Comment')->placeholder('No supervisor comment'),
                    \Filament\Infolists\Components\TextEntry::make('director_comment')->label('Director Comment')->placeholder('No director comment'),
                    \Filament\Infolists\Components\TextEntry::make('investigation_status')
                        ->label('Investigation Status')
                        ->placeholder('Not started')
                        ->formatStateUsing(fn (?string $state): string => filled($state) ? (Tip::getStatusLabels()[$state] ?? str($state)->replace('_', ' ')->title()->toString()) : 'Not started'),
                    \Filament\Infolists\Components\TextEntry::make('sub_city_notes')->label('Sub City Notes')->placeholder('No notes'),
                ])
                ->visible(fn (?Tip $record): bool => $record?->tip_source === Tip::SOURCE_CALL_CENTER),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTips::route('/'),
            'create' => Pages\CreateTip::route('/create'),
            'view' => Pages\ViewTip::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['createdBy']);
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('admin') || $user->can('manage_call_tip_workflow')) {
            return $query;
        }

        if ($user->can('review_supervisor_call_tips') || $user->can('review_director_call_tips')) {
            return $query->where('tip_source', Tip::SOURCE_CALL_CENTER);
        }

        if ($user->can('manage_sub_city_call_tips') && filled($user->sub_city)) {
            return $query
                ->where('tip_source', Tip::SOURCE_CALL_CENTER)
                ->where('sub_city', $user->sub_city);
        }

        if ($user->can('create_call_tips') || $user->can('view_own_call_tips')) {
            return $query
                ->where('tip_source', Tip::SOURCE_CALL_CENTER)
                ->where('created_by', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('admin') ||
            $user->can('manage_call_tip_workflow') ||
            $user->can('review_supervisor_call_tips') ||
            $user->can('review_director_call_tips') ||
            $user->can('manage_sub_city_call_tips') ||
            $user->can('create_call_tips') ||
            $user->can('view_own_call_tips')
        );
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return (bool) $user && ($user->hasRole('admin') || $user->can('create_call_tips'));
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    private static function canRunSupervisorAction(Tip $record): bool
    {
        $user = auth()->user();

        return (bool) $user
            && $record->tip_source === Tip::SOURCE_CALL_CENTER
            && $record->status === Tip::STATUS_PENDING_SUPERVISOR_REVIEW
            && ($user->hasRole('admin') || $user->can('review_supervisor_call_tips'));
    }

    private static function canRunDirectorAction(Tip $record): bool
    {
        $user = auth()->user();

        return (bool) $user
            && $record->tip_source === Tip::SOURCE_CALL_CENTER
            && $record->status === Tip::STATUS_PENDING_DIRECTOR_REVIEW
            && ($user->hasRole('admin') || $user->can('review_director_call_tips'));
    }

    private static function canRunSubCityAction(Tip $record): bool
    {
        $user = auth()->user();

        return (bool) $user
            && $record->tip_source === Tip::SOURCE_CALL_CENTER
            && in_array($record->status, [Tip::STATUS_DISPATCHED, Tip::STATUS_UNDER_INVESTIGATION], true)
            && (
                $user->hasRole('admin') ||
                ($user->can('manage_sub_city_call_tips') && filled($user->sub_city) && $user->sub_city === $record->sub_city)
            );
    }

    private static function isAdmin(): bool
    {
        return (bool) auth()->user()?->hasRole('admin');
    }
}
