<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ViolationRecordResource\Pages;
use App\Filament\Resources\ViolationRecordResource\RelationManagers\ConfiscatedAssetsRelationManager;
use App\Filament\Resources\ViolationRecordResource\RelationManagers\PenaltyReceiptRelationManager;
use App\Filament\Resources\ViolationRecordResource\RelationManagers\WarningLettersRelationManager;
use App\Models\SubCity;
use App\Models\User;
use App\Models\Violator;
use App\Models\ViolationRecord;
use App\Models\ViolationType;
use App\Models\Woreda;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Exports\ViolationRecordExporter;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViolationRecordResource extends Resource
{
    protected static ?string $model = ViolationRecord::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'am' ? 'ቅጣት እና እርምጃ' : 'Penalty & Action';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'am' ? 'የደንብ መተላለፍ' : 'Violation Records';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'am' ? 'የደንብ መተላለፍ' : 'Violation Record';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'am' ? 'የደንብ መተላለፎች' : 'Violation Records';
    }

    public static function form(Schema $schema): Schema
    {
        $isOfficer = auth()->user()?->hasRole('officer') && ! auth()->user()?->hasRole('admin');
        $am = app()->getLocale() === 'am';

        return $schema->schema([
            Section::make($am ? 'ደንብ ተላላፊ እና የጥፋት አይነት' : 'Violator & Violation Type')
                ->schema([
                    Forms\Components\Select::make('violator_id')
                        ->label($am ? 'ደንብ ተላላፊ' : 'Violator')
                        ->options(Violator::query()->orderBy('full_name_am')->get()->mapWithKeys(
                            fn ($v) => [$v->id => $v->full_name_am . ($v->phone ? " ({$v->phone})" : '')]
                        ))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            if ($state) {
                                $count = ViolationRecord::where('violator_id', $state)->count();
                                $set('repeat_offense_count', $count);
                            }
                        })
                        ->createOptionForm([
                            Forms\Components\Select::make('type')
                                ->options([
                                    'individual' => $am ? 'ግለሰብ' : 'Individual',
                                    'organization' => $am ? 'ድርጅት' : 'Organization',
                                ])
                                ->default('individual')
                                ->required(),
                            Forms\Components\TextInput::make('full_name_am')
                                ->label($am ? 'ሙሉ ስም' : 'Full Name (Amharic)')
                                ->required(),
                            Forms\Components\TextInput::make('phone')
                                ->label($am ? 'ስልክ' : 'Phone')
                                ->tel(),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return Violator::create($data)->id;
                        }),
                    Forms\Components\Select::make('violation_type_id')
                        ->label($am ? 'የጥፋት አይነት' : 'Violation Type')
                        ->options(ViolationType::active()->with('penaltySchedule')->get()->mapWithKeys(
                            fn ($v) => [$v->id => $v->display_name . ' - ETB ' . number_format($v->fine_amount, 2)]
                        ))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            if ($state) {
                                $vt = ViolationType::with('penaltySchedule')->find($state);
                                if ($vt) {
                                    $set('fine_amount', $vt->fine_amount);
                                    $set('regulation_number', $vt->regulation_reference);
                                }
                            }
                        }),
                ])
                ->columns(2),

            Section::make($am ? 'የተፈፀመበት ቦታ እና ጊዜ' : 'Location & Time')
                ->schema([
                    Forms\Components\Select::make('sub_city_id')
                        ->label($am ? 'ክፍለ ከተማ' : 'Sub City')
                        ->options(SubCity::pluck('name_am', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('woreda_id', null))
                        ->default(fn () => auth()->user()?->sub_city),
                    Forms\Components\Select::make('woreda_id')
                        ->label($am ? 'ወረዳ' : 'Woreda')
                        ->options(fn (Get $get) => $get('sub_city_id')
                            ? Woreda::where('sub_city_id', $get('sub_city_id'))->pluck('name_am', 'id')
                            : []
                        )
                        ->searchable()
                        ->default(fn () => auth()->user()?->woreda),
                    Forms\Components\TextInput::make('block')
                        ->label($am ? 'ብሎክ' : 'Block'),
                    Forms\Components\TextInput::make('specific_location')
                        ->label($am ? 'ልዩ ቦታ' : 'Specific Location'),
                    Forms\Components\DatePicker::make('violation_date')
                        ->label($am ? 'ቀን' : 'Date')
                        ->ethiopic()
                        ->firstDayOfWeek(1)
                        ->closeOnDateSelection()
                        ->default(now())
                        ->required(),
                    Forms\Components\TimePicker::make('violation_time')
                        ->label($am ? 'ሰዓት' : 'Time')
                        ->seconds(false),
                ])
                ->columns(3),

            Section::make($am ? 'ቅጣት እና ህጋዊ ማጣቀሻ' : 'Penalty & Legal Reference')
                ->schema([
                    Forms\Components\TextInput::make('fine_amount')
                        ->label($am ? 'የቅጣት መጠን (ብር)' : 'Fine Amount (Birr)')
                        ->numeric()
                        ->prefix('ETB')
                        ->minValue(0)
                        ->required()
                        ->readOnly()
                        ->hint($am ? 'ከጥፋት አይነት በራስ-ሰር ይሞላል' : 'Auto-filled from violation type'),
                    Forms\Components\TextInput::make('repeat_offense_count')
                        ->label($am ? 'ድግግሞሽ' : 'Repeat Offense Count')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->readOnly($isOfficer),
                    Forms\Components\TextInput::make('regulation_number')
                        ->label($am ? 'ደንብ ቁጥር' : 'Regulation Number')
                        ->readOnly(),
                    Forms\Components\TextInput::make('article')
                        ->label($am ? 'አንቀጽ' : 'Article')
                        ->readOnly($isOfficer),
                    Forms\Components\TextInput::make('sub_article')
                        ->label($am ? 'ንዑስ አንቀጽ' : 'Sub Article')
                        ->readOnly($isOfficer),
                ])
                ->columns(3),

            Section::make($am ? 'እርምጃ እና ሁኔታ' : 'Action & Status')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label($am ? 'ሁኔታ' : 'Status')
                        ->options([
                            'open' => $am ? 'ጅምር' : 'Open',
                            'warning_issued' => $am ? 'ማስጠንቀቂያ ተሰጥቷል' : 'Warning Issued',
                            'penalty_issued' => $am ? 'ቅጣት ተሰጥቷል' : 'Penalty Issued',
                            'payment_pending' => $am ? 'ክፍያ በመጠበቅ' : 'Payment Pending',
                            'paid' => $am ? 'ተከፍሏል' : 'Paid',
                            'court_filed' => $am ? 'ክስ ቀርቧል' : 'Court Filed',
                            'closed' => $am ? 'ያለቀ' : 'Closed',
                        ])
                        ->default('open')
                        ->required()
                        ->disabled($isOfficer),
                    Forms\Components\TextInput::make('action_taken')
                        ->label($am ? 'የተወሰደ እርምጃ' : 'Action Taken'),
                    Forms\Components\Hidden::make('reported_by')
                        ->default(auth()->id()),
                    Forms\Components\Select::make('verified_by')
                        ->label($am ? 'ያረጋገጠው ሽፍት መሪ' : 'Verified By (Shift Leader)')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->visible(! $isOfficer),
                    Forms\Components\Textarea::make('investigation_notes')
                        ->label($am ? 'ምርመራ' : 'Investigation Notes')
                        ->maxLength(8000)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        $am = app()->getLocale() === 'am';

        return $schema->schema([
            Section::make($am ? 'የእርምጃ ሂደት' : 'Escalation Progress')
                ->schema([
                    View::make('filament.resources.violation-record.escalation-progress'),
                ]),

            Section::make($am ? 'ደንብ ተላላፊ እና የጥፋት አይነት' : 'Violator & Violation Type')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('violator.full_name_am')
                            ->label($am ? 'ደንብ ተላላፊ' : 'Violator'),
                        TextEntry::make('violationType.name_am')
                            ->label($am ? 'የጥፋት አይነት' : 'Violation Type'),
                    ]),
                ]),

            Section::make($am ? 'የተፈፀመበት ቦታ እና ጊዜ' : 'Location & Time')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('subCity.name_am')
                            ->label($am ? 'ክፍለ ከተማ' : 'Sub City'),
                        TextEntry::make('woreda.name_am')
                            ->label($am ? 'ወረዳ' : 'Woreda'),
                        TextEntry::make('block')
                            ->label($am ? 'ብሎክ' : 'Block')
                            ->placeholder('-'),
                        TextEntry::make('specific_location')
                            ->label($am ? 'ልዩ ቦታ' : 'Specific Location')
                            ->placeholder('-'),
                        TextEntry::make('violation_date')
                            ->label($am ? 'ቀን' : 'Date')
                            ->date(),
                        TextEntry::make('violation_time')
                            ->label($am ? 'ሰዓት' : 'Time')
                            ->placeholder('-'),
                    ]),
                ]),

            Section::make($am ? 'ቅጣት እና ህጋዊ ማጣቀሻ' : 'Penalty & Legal Reference')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('fine_amount')
                            ->label($am ? 'የቅጣት መጠን' : 'Fine Amount')
                            ->money('ETB'),
                        TextEntry::make('repeat_offense_count')
                            ->label($am ? 'ድግግሞሽ' : 'Repeat Offense'),
                        TextEntry::make('regulation_number')
                            ->label($am ? 'ደንብ ቁጥር' : 'Regulation')
                            ->placeholder('-'),
                        TextEntry::make('article')
                            ->label($am ? 'አንቀጽ' : 'Article')
                            ->placeholder('-'),
                        TextEntry::make('sub_article')
                            ->label($am ? 'ንዑስ አንቀጽ' : 'Sub Article')
                            ->placeholder('-'),
                    ]),
                ]),

            Section::make($am ? 'ሁኔታ እና ኦፊሰር' : 'Status & Officers')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('status')
                            ->label($am ? 'ሁኔታ' : 'Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'open' => $am ? 'ጅምር' : 'Open',
                                'warning_issued' => $am ? 'ማስጠንቀቂያ' : 'Warning Issued',
                                'penalty_issued' => $am ? 'ቅጣት ተሰጥቷል' : 'Penalty Issued',
                                'payment_pending' => $am ? 'ክፍያ በመጠበቅ' : 'Payment Pending',
                                'paid' => $am ? 'ተከፍሏል' : 'Paid',
                                'court_filed' => $am ? 'ክስ ቀርቧል' : 'Court Filed',
                                'closed' => $am ? 'ያለቀ' : 'Closed',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'open' => 'secondary',
                                'warning_issued' => 'warning',
                                'penalty_issued' => 'info',
                                'payment_pending' => 'warning',
                                'paid' => 'success',
                                'court_filed' => 'danger',
                                'closed' => 'success',
                                default => 'secondary',
                            }),
                        TextEntry::make('action_taken')
                            ->label($am ? 'የተወሰደ እርምጃ' : 'Action Taken')
                            ->placeholder('-'),
                        TextEntry::make('reportedByUser.name')
                            ->label($am ? 'ያሳወቀው ኦፊሰር' : 'Reported By'),
                        TextEntry::make('verifiedByUser.name')
                            ->label($am ? 'ያረጋገጠው' : 'Verified By')
                            ->placeholder($am ? 'ገና አልተረጋገጠም' : 'Not verified yet'),
                        TextEntry::make('investigation_notes')
                            ->label($am ? 'ምርመራ' : 'Investigation Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('violator.full_name_am')
                    ->label(app()->getLocale() === 'am' ? 'ደንብ ተላላፊ' : 'Violator')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('violationType.name_am')
                    ->label(app()->getLocale() === 'am' ? 'የጥፋት አይነት' : 'Violation Type')
                    ->searchable()
                    ->wrap()
                    ->limit(40),
                Tables\Columns\TextColumn::make('violation_date')
                    ->label(app()->getLocale() === 'am' ? 'ቀን' : 'Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fine_amount')
                    ->label(app()->getLocale() === 'am' ? 'ቅጣት' : 'Fine')
                    ->money('ETB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subCity.name_am')
                    ->label(app()->getLocale() === 'am' ? 'ክ/ከተማ' : 'Sub City')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('block')
                    ->label(app()->getLocale() === 'am' ? 'ብሎክ' : 'Block')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('repeat_offense_count')
                    ->label(app()->getLocale() === 'am' ? 'ድግግሞሽ' : 'Repeat')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(app()->getLocale() === 'am' ? 'ሁኔታ' : 'Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => app()->getLocale() === 'am' ? 'ጅምር' : 'Open',
                        'warning_issued' => app()->getLocale() === 'am' ? 'ማስጠንቀቂ��' : 'Warning',
                        'penalty_issued' => app()->getLocale() === 'am' ? 'ቅጣት' : 'Penalized',
                        'payment_pending' => app()->getLocale() === 'am' ? 'ክፍያ' : 'Payment',
                        'paid' => app()->getLocale() === 'am' ? 'ተከፍሏል' : 'Paid',
                        'court_filed' => app()->getLocale() === 'am' ? 'ክስ' : 'Court',
                        'closed' => app()->getLocale() === 'am' ? 'ያለቀ' : 'Closed',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'secondary',
                        'warning_issued' => 'warning',
                        'penalty_issued' => 'info',
                        'payment_pending' => 'warning',
                        'paid' => 'success',
                        'court_filed' => 'danger',
                        'closed' => 'success',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('reportedByUser.name')
                    ->label(app()->getLocale() === 'am' ? 'ኦፊሰር' : 'Officer')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('violation_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(app()->getLocale() === 'am' ? 'ሁኔታ' : 'Status')
                    ->options([
                        'open' => app()->getLocale() === 'am' ? 'ጅምር' : 'Open',
                        'warning_issued' => app()->getLocale() === 'am' ? 'ማስጠንቀቂያ' : 'Warning Issued',
                        'penalty_issued' => app()->getLocale() === 'am' ? 'ቅጣት ተሰጥቷል' : 'Penalty Issued',
                        'payment_pending' => app()->getLocale() === 'am' ? 'ክፍያ በመጠበቅ' : 'Payment Pending',
                        'paid' => app()->getLocale() === 'am' ? 'ተከፍሏል' : 'Paid',
                        'court_filed' => app()->getLocale() === 'am' ? 'ክስ ቀርቧል' : 'Court Filed',
                        'closed' => app()->getLocale() === 'am' ? 'ያለቀ' : 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('sub_city_id')
                    ->label(app()->getLocale() === 'am' ? 'ክፍለ ከተማ' : 'Sub City')
                    ->options(SubCity::pluck('name_am', 'id')),
                Tables\Filters\SelectFilter::make('violation_type_id')
                    ->label(app()->getLocale() === 'am' ? 'የጥፋት አይነት' : 'Violation Type')
                    ->options(ViolationType::pluck('name_am', 'id'))
                    ->searchable(),
            ])
            ->headerActions([
                Actions\ExportAction::make()
                    ->exporter(ViolationRecordExporter::class)
                    ->formats([ExportFormat::Csv]),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
                Actions\ExportBulkAction::make()
                    ->exporter(ViolationRecordExporter::class)
                    ->formats([ExportFormat::Csv]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PenaltyReceiptRelationManager::class, // relationship: penaltyReceipts
            WarningLettersRelationManager::class,
            ConfiscatedAssetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListViolationRecords::route('/'),
            'create' => Pages\CreateViolationRecord::route('/create'),
            'view' => Pages\ViewViolationRecord::route('/{record}'),
            'edit' => Pages\EditViolationRecord::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('admin')
            || $user->can('manage_penalty_action')
            || $user->can('view_violation_records')
            || $user->can('create_violation_records')
            || $user->can('view_sub_city_violations')
        );
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('admin')
            || $user->can('manage_penalty_action')
            || $user->can('create_violation_records')
        );
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin') || $user->can('manage_penalty_action') || $user->can('edit_violation_records')) {
            return true;
        }

        // Officers can only edit their own records
        if ($user->can('create_violation_records') && $record->reported_by === $user->id) {
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
        $query = parent::getEloquentQuery()->with(['violator', 'violationType', 'subCity', 'reportedByUser']);

        if (! $user) {
            return $query;
        }

        if ($user->hasRole('admin') || $user->can('manage_penalty_action')) {
            return $query;
        }

        if ($user->hasRole('supervisor')) {
            return $query
                ->when($user->sub_city, fn (Builder $q) => $q->where('sub_city_id', $user->sub_city))
                ->when($user->woreda, fn (Builder $q) => $q->where('woreda_id', $user->woreda));
        }

        if ($user->hasRole('officer')) {
            return $query
                ->where('reported_by', $user->id)
                ->when($user->sub_city, fn (Builder $q) => $q->where('sub_city_id', $user->sub_city))
                ->when($user->woreda, fn (Builder $q) => $q->where('woreda_id', $user->woreda));
        }

        return $query;
    }
}
