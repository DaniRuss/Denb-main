<?php

namespace App\Filament\Resources;

use App\Models\UniformInventory;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use App\Filament\Resources\UniformInventories\Pages;

class UniformInventoryResource extends Resource
{
    protected static ?string $model = UniformInventory::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    protected static string|\UnitEnum|null $navigationGroup = 'Human Resources';
    protected static ?string $navigationLabel = 'Uniform Inventory';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make('Item Details')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('item_name')
                        ->label('Item Name (English)')
                        ->required()
                        ->maxLength(255),

                    \Filament\Forms\Components\TextInput::make('item_name_am')
                        ->label('Item Name (አማርኛ)')
                        ->maxLength(255),

                    \Filament\Forms\Components\Select::make('category')
                        ->label('Category')
                        ->options([
                            'uniform_top' => 'Uniform Top / Shirt',
                            'uniform_bottom' => 'Uniform Pants / Skirt',
                            'footwear' => 'Footwear',
                            'headgear' => 'Headgear / Cap',
                            'belt_accessories' => 'Belts & Accessories',
                            'winter_gear' => 'Winter Gear',
                            'protective_equipment' => 'Protective Equipment',
                            'other' => 'Other',
                        ])
                        ->required(),

                    \Filament\Forms\Components\TextInput::make('size')
                        ->label('Size')
                        ->maxLength(20)
                        ->placeholder('e.g. M, L, XL, 42'),

                    \Filament\Forms\Components\TextInput::make('quantity_in_stock')
                        ->label('Quantity in Stock')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    \Filament\Forms\Components\TextInput::make('min_stock_level')
                        ->label('Minimum Stock Level')
                        ->numeric()
                        ->default(10),

                    \Filament\Forms\Components\TextInput::make('unit')
                        ->label('Unit')
                        ->default('pieces')
                        ->maxLength(30),

                    \Filament\Forms\Components\TextInput::make('location')
                        ->label('Storage Location')
                        ->maxLength(100),

                    \Filament\Forms\Components\TextInput::make('supplier')
                        ->label('Supplier')
                        ->maxLength(255),

                    \Filament\Forms\Components\TextInput::make('unit_cost')
                        ->label('Unit Cost (ETB)')
                        ->numeric()
                        ->prefix('ETB')
                        ->nullable(),

                    \Filament\Forms\Components\Toggle::make('is_active')
                        ->label('Item Active / Available')
                        ->default(true),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucwords(str_replace('_', ' ', $state ?? ''))),

                Tables\Columns\TextColumn::make('size')
                    ->label('Size'),

                Tables\Columns\TextColumn::make('quantity_in_stock')
                    ->label('In Stock')
                    ->sortable()
                    ->color(fn($record) => $record->quantity_in_stock <= $record->min_stock_level ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('min_stock_level')
                    ->label('Min Stock'),

                Tables\Columns\IconColumn::make('is_low_stock')
                    ->label('Low Stock')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->quantity_in_stock <= $record->min_stock_level)
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location'),

                Tables\Columns\TextColumn::make('supplier')
                    ->label('Supplier')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')->options([
                    'uniform_top' => 'Uniform Top',
                    'uniform_bottom' => 'Uniform Bottom',
                    'footwear' => 'Footwear',
                    'headgear' => 'Headgear',
                    'belt_accessories' => 'Accessories',
                    'protective_equipment' => 'Protective Equipment',
                ]),
            ])
            ->defaultSort('quantity_in_stock')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $lowStock = UniformInventory::whereColumn('quantity_in_stock', '<=', 'min_stock_level')->count();
        return $lowStock > 0 ? (string) $lowStock : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUniformInventories::route('/'),
            'create' => Pages\CreateUniformInventory::route('/create'),
            'edit' => Pages\EditUniformInventory::route('/{record}/edit'),
        ];
    }
}
