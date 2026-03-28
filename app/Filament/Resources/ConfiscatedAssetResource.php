<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConfiscatedAssetResource\Pages;
use App\Models\ConfiscatedAsset;
use App\Models\VolunteerTip;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ConfiscatedAssetResource extends Resource
{
    protected static ?string $model = ConfiscatedAsset::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    protected static ?int    $navigationSort   = 4;

    public static function getNavigationLabel(): string
    {
        return __('Confiscated Assets');
    }

    public static function getModelLabel(): string
    {
        return __('Confiscated Asset');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Confiscated Assets');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Awareness Management');
    }

    // Only Officers and Admins can manage confiscated assets
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['officer', 'admin', 'super_admin']) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make(__('Seized Item Details'))
                ->icon('heroicon-o-archive-box')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('volunteer_tip_id')
                            ->label(__('Linked Volunteer Tip'))
                            ->options(
                                VolunteerTip::whereIn('status', ['verified', 'resolved'])
                                    ->get()
                                    ->pluck('tip_code', 'id')
                            )
                            ->searchable()
                            ->required()
                            ->prefixIcon('heroicon-m-light-bulb'),

                        Forms\Components\TextInput::make('item_description')
                            ->label(__('Item Description'))
                            ->required()
                            ->maxLength(255),
                    ]),

                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('seizure_location')
                            ->label(__('Seizure Location'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('seizure_date')
                            ->label(__('Date of Seizure'))
                            ->required()
                            ->default(now()),
                    ]),

                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('estimated_value')
                            ->label(__('Estimated Value (ETB)'))
                            ->numeric()
                            ->prefix('ETB'),

                        Forms\Components\Select::make('handover_status')
                            ->label(__('Handover Status'))
                            ->options([
                                'impounded'  => __('Impounded'),
                                'auctioned'  => __('Auctioned'),
                                'destroyed'  => __('Destroyed'),
                                'returned'   => __('Returned'),
                            ])
                            ->default('impounded')
                            ->required(),
                    ]),

                    Forms\Components\Textarea::make('notes')
                        ->label(__('Notes'))
                        ->rows(3)
                        ->columnSpanFull(),

                    // Auto-fill the seizing officer from logged-in user
                    Forms\Components\Hidden::make('seized_by')
                        ->default(fn () => auth()->id()),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('volunteerTip.tip_code')
                    ->label(__('Tip Ref.'))
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('item_description')
                    ->label(__('Item'))
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('seizure_location')
                    ->label(__('Location'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('seizure_date')
                    ->label(__('Seizure Date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_value')
                    ->label(__('Value (ETB)'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('handover_status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'impounded'  => 'warning',
                        'auctioned'  => 'info',
                        'destroyed'  => 'danger',
                        'returned'   => 'success',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('seizedBy.name')
                    ->label(__('Seized By'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Recorded At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('handover_status')
                    ->options([
                        'impounded'  => __('Impounded'),
                        'auctioned'  => __('Auctioned'),
                        'destroyed'  => __('Destroyed'),
                        'returned'   => __('Returned'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListConfiscatedAssets::route('/'),
            'view'   => Pages\ViewConfiscatedAsset::route('/{record}'),
            'edit'   => Pages\EditConfiscatedAsset::route('/{record}/edit'),
        ];
    }
}
