<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ViolatorResource\Pages;
use App\Filament\Resources\ViolatorResource\RelationManagers\ViolationRecordsRelationManager;
use App\Models\SubCity;
use App\Models\Violator;
use App\Models\Woreda;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViolatorResource extends Resource
{
    protected static ?string $model = Violator::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'am' ? 'ቅጣት እና እርምጃ' : 'Penalty & Action';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'am' ? 'ደንብ ተላላፊዎች' : 'Violators';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'am' ? 'ደንብ ተላላፊ' : 'Violator';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'am' ? 'ደንብ ተላላፊዎች' : 'Violators';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(app()->getLocale() === 'am' ? 'የደንብ ተላላፊ መረጃ' : 'Violator Information')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label(app()->getLocale() === 'am' ? 'አይነት' : 'Type')
                        ->options([
                            'individual' => app()->getLocale() === 'am' ? 'ግለሰብ' : 'Individual',
                            'organization' => app()->getLocale() === 'am' ? 'ድርጅት' : 'Organization',
                        ])
                        ->default('individual')
                        ->required()
                        ->live(),
                    Forms\Components\TextInput::make('full_name_am')
                        ->label(app()->getLocale() === 'am' ? 'ሙሉ ስም (አማርኛ)' : 'Full Name (Amharic)')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('full_name_en')
                        ->label(app()->getLocale() === 'am' ? 'ሙሉ ስም (እንግሊዝኛ)' : 'Full Name (English)')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->label(app()->getLocale() === 'am' ? 'ስልክ ቁጥር' : 'Phone')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('id_number')
                        ->label(app()->getLocale() === 'am' ? 'መታወቂያ ቁጥር' : 'ID Number')
                        ->maxLength(50),
                ])
                ->columns(2),

            Section::make(app()->getLocale() === 'am' ? 'አድራሻ' : 'Address')
                ->schema([
                    Forms\Components\Select::make('sub_city_id')
                        ->label(app()->getLocale() === 'am' ? 'ክፍለ ከተማ' : 'Sub City')
                        ->options(SubCity::pluck('name_am', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('woreda_id', null)),
                    Forms\Components\Select::make('woreda_id')
                        ->label(app()->getLocale() === 'am' ? 'ወረዳ' : 'Woreda')
                        ->options(fn (Get $get) => $get('sub_city_id')
                            ? Woreda::where('sub_city_id', $get('sub_city_id'))->pluck('name_am', 'id')
                            : []
                        )
                        ->searchable(),
                    Forms\Components\TextInput::make('specific_location')
                        ->label(app()->getLocale() === 'am' ? 'ልዩ ቦታ' : 'Specific Location')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('house_number')
                        ->label(app()->getLocale() === 'am' ? 'ቤት ቁጥር' : 'House Number')
                        ->maxLength(50),
                ])
                ->columns(2),

            Section::make(app()->getLocale() === 'am' ? 'ተጨማሪ' : 'Additional')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label(app()->getLocale() === 'am' ? 'ማስታወሻ' : 'Notes')
                        ->maxLength(5000)
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name_am')
                    ->label(app()->getLocale() === 'am' ? 'ስም' : 'Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(app()->getLocale() === 'am' ? 'አይነት' : 'Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'individual' => app()->getLocale() === 'am' ? 'ግለሰብ' : 'Individual',
                        'organization' => app()->getLocale() === 'am' ? 'ድርጅት' : 'Organization',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'individual' => 'info',
                        'organization' => 'warning',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('phone')
                    ->label(app()->getLocale() === 'am' ? 'ስልክ' : 'Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subCity.name_am')
                    ->label(app()->getLocale() === 'am' ? 'ክፍለ ከተማ' : 'Sub City')
                    ->sortable(),
                Tables\Columns\TextColumn::make('woreda.name_am')
                    ->label(app()->getLocale() === 'am' ? 'ወረዳ' : 'Woreda')
                    ->sortable(),
                Tables\Columns\TextColumn::make('violation_records_count')
                    ->label(app()->getLocale() === 'am' ? 'ጥሰቶች' : 'Violations')
                    ->counts('violationRecords')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'individual' => app()->getLocale() === 'am' ? 'ግለሰብ' : 'Individual',
                        'organization' => app()->getLocale() === 'am' ? 'ድርጅት' : 'Organization',
                    ]),
                Tables\Filters\SelectFilter::make('sub_city_id')
                    ->label(app()->getLocale() === 'am' ? 'ክፍለ ከተማ' : 'Sub City')
                    ->options(SubCity::pluck('name_am', 'id')),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ViolationRecordsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListViolators::route('/'),
            'create' => Pages\CreateViolator::route('/create'),
            'view' => Pages\ViewViolator::route('/{record}'),
            'edit' => Pages\EditViolator::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('admin')
            || $user->can('manage_penalty_action')
            || $user->can('manage_violators')
            || $user->can('view_violation_records')
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
            || $user->can('manage_violators')
        );
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        return (bool) $user && (
            $user->hasRole('admin')
            || $user->can('manage_penalty_action')
            || $user->can('manage_violators')
        );
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        return (bool) $user && ($user->hasRole('admin') || $user->can('manage_penalty_action'));
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['subCity', 'woreda']);

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
                ->whereHas('violationRecords', fn (Builder $q) => $q->where('reported_by', $user->id))
                ->when($user->sub_city, fn (Builder $q) => $q->where('sub_city_id', $user->sub_city))
                ->when($user->woreda, fn (Builder $q) => $q->where('woreda_id', $user->woreda));
        }

        return $query;
    }
}
