<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IllegalAssetResource\Pages;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\HandoversRelationManager;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\EstimationsRelationManager;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\TransfersRelationManager;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\SalesRelationManager;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\DisposalsRelationManager;
use App\Models\IllegalAsset;
use App\Models\Department;
use App\Models\Officer;
use App\Models\AssetHandover;
use App\Models\AssetEstimation;
use App\Models\AssetTransfer;
use App\Models\AssetSale;
use App\Models\AssetDisposal;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;

class IllegalAssetResource extends Resource
{
    protected static ?string $model = IllegalAsset::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    protected static string|\UnitEnum|null $navigationGroup = 'Asset Management';
    protected static ?string $navigationLabel = 'Illegal Assets';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Asset Registration Details')
                    ->schema([
                        Forms\Components\TextInput::make('asset_type')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_confiscated')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('location_found')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('department_id')
                            ->label('Department Confiscated By')
                            ->relationship('department', 'name_en')
                            ->searchable()
                            ->nullable(),
                        Forms\Components\Select::make('officer_id')
                            ->label('Confiscating Officer')
                            ->relationship('officer', 'badge_number')
                            ->searchable()
                            ->nullable(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'Registered' => 'Registered',
                                'Handed Over' => 'Handed Over',
                                'Estimated' => 'Estimated',
                                'Transferred' => 'Transferred',
                                'Sold' => 'Sold',
                                'Disposed' => 'Disposed',
                            ])
                            ->required()
                            ->default('Registered'),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('asset_type')->searchable(),
                Tables\Columns\TextColumn::make('date_confiscated')->date()->sortable(),
                Tables\Columns\TextColumn::make('location_found')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Registered'  => 'primary',
                        'Handed Over' => 'warning',
                        'Estimated'   => 'info',
                        'Transferred' => 'gray',
                        'Sold'        => 'success',
                        'Disposed'    => 'danger',
                        default       => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('department.name_en')->label('Department')->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Registered' => 'Registered',
                        'Handed Over' => 'Handed Over',
                        'Estimated' => 'Estimated',
                        'Transferred' => 'Transferred',
                        'Sold' => 'Sold',
                        'Disposed' => 'Disposed',
                    ]),
                Tables\Filters\SelectFilter::make('department_id')
                    ->relationship('department', 'name_en')
                    ->label('Department'),
            ])
            ->actions([
                EditAction::make(),
                
                // Asset Handover Modal Action
                Action::make('handover')
                    ->label('Hand Over')
                    ->icon('heroicon-o-hand-raised')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'Registered')
                    ->form([
                        Forms\Components\Select::make('department_id')
                            ->label('Hand Over To Department')
                            ->options(Department::pluck('name_en', 'id')->toArray())
                            ->required(),
                        Forms\Components\Select::make('handed_over_to_officer_id')
                            ->label('To Officer')
                            ->options(Officer::pluck('badge_number', 'id')->toArray())
                            ->required(),
                        Forms\Components\DatePicker::make('handover_date')
                            ->default(now())->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (array $data, $record): void {
                        AssetHandover::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Handed Over']);
                    }),

                // Asset Estimation Modal Action
                Action::make('estimate')
                    ->label('Estimate')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->visible(fn ($record) => in_array($record->status, ['Registered', 'Handed Over']))
                    ->form([
                        Forms\Components\TextInput::make('estimated_value')
                            ->numeric()->prefix('$')->required(),
                        Forms\Components\TextInput::make('evaluator_name')->required(),
                        Forms\Components\DatePicker::make('evaluation_date')->default(now())->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (array $data, $record): void {
                        AssetEstimation::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Estimated']);
                    }),

                // Asset Transfer Modal Action
                Action::make('transfer')
                    ->label('Transfer')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('purple')
                    ->visible(fn ($record) => in_array($record->status, ['Handed Over', 'Estimated']))
                    ->form([
                        Forms\Components\Select::make('from_department_id')
                            ->label('From Dept')
                            ->options(Department::pluck('name_en', 'id')->toArray())
                            ->required(),
                        Forms\Components\Select::make('to_department_id')
                            ->label('To Dept')
                            ->options(Department::pluck('name_en', 'id')->toArray())
                            ->required(),
                        Forms\Components\TextInput::make('from_storage_facility')->label('From Facility'),
                        Forms\Components\TextInput::make('to_storage_facility')->label('To Facility'),
                        Forms\Components\Select::make('transferred_by_officer_id')
                            ->options(Officer::pluck('badge_number', 'id')->toArray())
                            ->label('Transferred By')->required(),
                        Forms\Components\DatePicker::make('transfer_date')->default(now())->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (array $data, $record): void {
                        AssetTransfer::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Transferred']);
                    }),

                // Asset Sale Modal Action
                Action::make('sell')
                    ->label('Sell')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['Estimated', 'Transferred']))
                    ->form([
                        Forms\Components\TextInput::make('buyer_name')->required(),
                        Forms\Components\TextInput::make('buyer_contact'),
                        Forms\Components\TextInput::make('sale_price')->numeric()->prefix('$')->required(),
                        Forms\Components\Select::make('sold_by_officer_id')
                            ->options(Officer::pluck('badge_number', 'id')->toArray())
                            ->label('Sold By')->required(),
                        Forms\Components\DatePicker::make('sale_date')->default(now())->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (array $data, $record): void {
                        AssetSale::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Sold']);
                    }),

                // Asset Disposal Modal Action
                Action::make('dispose')
                    ->label('Dispose')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn ($record) => !in_array($record->status, ['Sold', 'Disposed']))
                    ->form([
                        Forms\Components\Select::make('disposal_method')
                            ->options([
                                'Destruction' => 'Destruction',
                                'Recycling' => 'Recycling',
                                'Government storage' => 'Government storage',
                                'Other' => 'Other',
                            ])->required(),
                        Forms\Components\Select::make('disposed_by_officer_id')
                            ->options(Officer::pluck('badge_number', 'id')->toArray())
                            ->label('Disposed By')->required(),
                        Forms\Components\DatePicker::make('disposal_date')->default(now())->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (array $data, $record): void {
                        AssetDisposal::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Disposed']);
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            HandoversRelationManager::class,
            EstimationsRelationManager::class,
            TransfersRelationManager::class,
            SalesRelationManager::class,
            DisposalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIllegalAssets::route('/'),
            'create' => Pages\CreateIllegalAsset::route('/create'),
            'edit' => Pages\EditIllegalAsset::route('/{record}/edit'),
        ];
    }
}
