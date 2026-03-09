<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UniformDistributions\Pages;
use App\Models\UniformDistribution;
use App\Models\Employee;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class UniformDistributionResource extends Resource
{
    protected static ?string $model = UniformDistribution::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static string|\UnitEnum|null $navigationGroup = 'Human Resources';
    protected static ?string $navigationLabel = 'Uniform Distribution';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Distribution Details')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Employee')
                            ->relationship('employee', 'first_name_en')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name_en} {$record->last_name_en} ({$record->employee_id})")
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('item_type')
                            ->label('Item Type')
                            ->options([
                                'shirt' => 'Shirt',
                                'pant' => 'Pant',
                                'jacket' => 'Jacket',
                                'rain_coat' => 'Rain Coat',
                                't_shirt' => 'T-Shirt',
                                'hat' => 'Hat',
                                'shoe_casual' => 'Shoe Casual',
                                'shoe_leather' => 'Shoe Leather',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('size')
                            ->label('Size')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->minValue(1),

                        Forms\Components\DatePicker::make('distribution_date')
                            ->label('Distribution Date')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('distribution_type')
                            ->label('Distribution Type')
                            ->options([
                                'new' => 'New Issue',
                                'replacement' => 'Replacement',
                                'additional' => 'Additional',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('reason')
                            ->label('Reason')
                            ->maxLength(255),

                        Forms\Components\Select::make('issued_by')
                            ->label('Issued By')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->default(auth()->id())
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.employee_id')
                    ->label('Emp ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->getStateUsing(fn($record) => "{$record->employee->first_name_en} {$record->employee->last_name_en}")
                    ->searchable(['first_name_en', 'last_name_en']),

                Tables\Columns\TextColumn::make('item_type')
                    ->label('Item')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('size')
                    ->label('Size'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('distribution_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('distribution_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'success',
                        'replacement' => 'warning',
                        'additional' => 'info',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('issuedBy.name')
                    ->label('Issued By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('item_type')
                    ->options([
                        'shirt' => 'Shirt',
                        'pant' => 'Pant',
                        'jacket' => 'Jacket',
                        'rain_coat' => 'Rain Coat',
                        't_shirt' => 'T-Shirt',
                        'hat' => 'Hat',
                        'shoe_casual' => 'Shoe Casual',
                        'shoe_leather' => 'Shoe Leather',
                    ]),
                Tables\Filters\SelectFilter::make('distribution_type')
                    ->options([
                        'new' => 'New',
                        'replacement' => 'Replacement',
                        'additional' => 'Additional',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUniformDistributions::route('/'),
            'create' => Pages\CreateUniformDistribution::route('/create'),
            'view' => Pages\ViewUniformDistribution::route('/{record}'),
            'edit' => Pages\EditUniformDistribution::route('/{record}/edit'),
        ];
    }
}
