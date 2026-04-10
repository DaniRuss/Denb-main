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
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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
        return $schema->schema([
            Section::make(app()->getLocale() === 'am' ? 'ደንብ ተላላፊ እና የጥፋት አይነት' : 'Violator & Violation Type')
                ->schema([
                    Forms\Components\Select::make('violator_id')
                        ->label(app()->getLocale() === 'am' ? 'ደንብ ተላላፊ' : 'Violator')
                        ->options(Violator::query()->orderBy('full_name_am')->get()->mapWithKeys(
                            fn ($v) => [$v->id => $v->full_name_am . ($v->phone ? " ({$v->phone})" : '')]
                        ))
                        ->searchable()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\Select::make('type')
                                ->options([
                                    'individual' => app()->getLocale() === 'am' ? 'ግለሰብ' : 'Individual',
                                    'organization' => app()->getLocale() === 'am' ? 'ድርጅት' : 'Organization',
                                ])
                                ->default('individual')
                                ->required(),
                            Forms\Components\TextInput::make('full_name_am')
                                ->label(app()->getLocale() === 'am' ? 'ሙሉ ስም' : 'Full Name (Amharic)')
                                ->required(),
                            Forms\Components\TextInput::make('phone')
                                ->label(app()->getLocale() === 'am' ? 'ስልክ' : 'Phone')
                                ->tel(),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return Violator::create($data)->id;
                        }),
                    Forms\Components\Select::make('violation_type_id')
                        ->label(app()->getLocale() === 'am' ? 'የጥፋት አይነት' : 'Violation Type')
                        ->options(ViolationType::active()->get()->mapWithKeys(
                            fn ($v) => [$v->id => $v->display_name . " - ETB {$v->fine_amount}"]
                        ))
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            if ($state) {
                                $vt = ViolationType::find($state);
                                if ($vt) {
                                    $set('fine_amount', $vt->fine_amount);
                                    if ($vt->penaltySchedule) {
                                        $set('regulation_number', $vt->regulation_reference);
                                    }
                                }
                            }
                        }),
                ])
                ->columns(2),

            Section::make(app()->getLocale() === 'am' ? 'የተፈፀመበት ቦታ እና ጊዜ' : 'Location & Time')
                ->schema([
                    Forms\Components\Select::make('sub_city_id')
                        ->label(app()->getLocale() === 'am' ? 'ክፍለ ከተማ' : 'Sub City')
                        ->options(SubCity::pluck('name_am', 'id'))
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('woreda_id', null)),
                    Forms\Components\Select::make('woreda_id')
                        ->label(app()->getLocale() === 'am' ? 'ወረዳ' : 'Woreda')
                        ->options(fn (Forms\Get $get) => $get('sub_city_id')
                            ? Woreda::where('sub_city_id', $get('sub_city_id'))->pluck('name_am', 'id')
                            : []
                        )
                        ->searchable(),
                    Forms\Components\TextInput::make('block')
                        ->label(app()->getLocale() === 'am' ? 'ብሎክ' : 'Block'),
                    Forms\Components\TextInput::make('specific_location')
                        ->label(app()->getLocale() === 'am' ? 'ልዩ ቦታ' : 'Specific Location'),
                    Forms\Components\DatePicker::make('violation_date')
                        ->label(app()->getLocale() === 'am' ? 'ቀን' : 'Date')
                        ->ethiopic()
                        ->firstDayOfWeek(1)
                        ->closeOnDateSelection()
                        ->default(now())
                        ->required(),
                    Forms\Components\TimePicker::make('violation_time')
                        ->label(app()->getLocale() === 'am' ? 'ሰዓት' : 'Time')
                        ->seconds(false),
                ])
                ->columns(3),

            Section::make(app()->getLocale() === 'am' ? 'ቅጣት እና ህጋዊ ማጣቀሻ' : 'Penalty & Legal Reference')
                ->schema([
                    Forms\Components\TextInput::make('fine_amount')
                        ->label(app()->getLocale() === 'am' ? 'የቅጣት መጠን (ብር)' : 'Fine Amount (Birr)')
                        ->numeric()
                        ->prefix('ETB')
                        ->required(),
                    Forms\Components\TextInput::make('repeat_offense_count')
                        ->label(app()->getLocale() === 'am' ? 'ድግግሞሽ' : 'Repeat Offense Count')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Forms\Components\TextInput::make('regulation_number')
                        ->label(app()->getLocale() === 'am' ? 'ደንብ ቁጥር' : 'Regulation Number'),
                    Forms\Components\TextInput::make('article')
                        ->label(app()->getLocale() === 'am' ? 'አንቀጽ' : 'Article'),
                    Forms\Components\TextInput::make('sub_article')
                        ->label(app()->getLocale() === 'am' ? 'ንዑስ አንቀጽ' : 'Sub Article'),
                ])
                ->columns(3),

            Section::make(app()->getLocale() === 'am' ? 'እርምጃ እና ሁኔታ' : 'Action & Status')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label(app()->getLocale() === 'am' ? 'ሁኔታ' : 'Status')
                        ->options([
                            'open' => app()->getLocale() === 'am' ? 'ጅምር' : 'Open',
                            'warning_issued' => app()->getLocale() === 'am' ? 'ማስጠንቀቂያ ተሰጥቷል' : 'Warning Issued',
                            'penalty_issued' => app()->getLocale() === 'am' ? 'ቅጣት ተሰጥቷል' : 'Penalty Issued',
                            'payment_pending' => app()->getLocale() === 'am' ? 'ክፍያ በመጠበቅ' : 'Payment Pending',
                            'paid' => app()->getLocale() === 'am' ? 'ተከፍሏል' : 'Paid',
                            'court_filed' => app()->getLocale() === 'am' ? 'ክስ ቀርቧል' : 'Court Filed',
                            'closed' => app()->getLocale() === 'am' ? 'ያለቀ' : 'Closed',
                        ])
                        ->default('open')
                        ->required(),
                    Forms\Components\TextInput::make('action_taken')
                        ->label(app()->getLocale() === 'am' ? 'የተወሰደ እርምጃ' : 'Action Taken'),
                    Forms\Components\Select::make('reported_by')
                        ->label(app()->getLocale() === 'am' ? 'ያሳወቀው ኦፊሰር' : 'Reported By')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->default(auth()->id())
                        ->required(),
                    Forms\Components\Select::make('verified_by')
                        ->label(app()->getLocale() === 'am' ? 'ያረጋገጠው ሽፍት መሪ' : 'Verified By (Shift Leader)')
                        ->options(User::pluck('name', 'id'))
                        ->searchable(),
                    Forms\Components\Textarea::make('investigation_notes')
                        ->label(app()->getLocale() === 'am' ? 'ምርመራ' : 'Investigation Notes')
                        ->maxLength(8000)
                        ->columnSpanFull(),
                ])
                ->columns(2),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PenaltyReceiptRelationManager::class,
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

        // Admin and penalty_action_officer see everything
        if ($user->hasRole('admin') || $user->can('manage_penalty_action')) {
            return $query;
        }

        // Sub-city officers see violations in their sub-city
        if ($user->can('view_sub_city_violations') && $user->sub_city) {
            return $query->where('sub_city_id', $user->sub_city);
        }

        // Supervisors see violations in their woreda/sub-city
        if ($user->hasRole('supervisor')) {
            if ($user->woreda) {
                return $query->where('woreda_id', $user->woreda);
            }
            if ($user->sub_city) {
                return $query->where('sub_city_id', $user->sub_city);
            }

            return $query;
        }

        // Officers see only violations they reported
        if ($user->hasRole('officer')) {
            return $query->where('reported_by', $user->id);
        }

        return $query;
    }
}
