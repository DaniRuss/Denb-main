<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IllegalAssetResource\Pages;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\HandoversRelationManager;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\EstimationsRelationManager;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\TransfersRelationManager;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\SalesRelationManager;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\DisposalsRelationManager;
use App\Filament\Resources\IllegalAssetResource\RelationManagers\ActivitiesRelationManager;
use App\Models\IllegalAsset;
use App\Models\Department;
use App\Models\Officer;
use App\Models\AssetHandover;
use App\Models\AssetEstimation;
use App\Models\AssetTransfer;
use App\Models\AssetSale;
use App\Models\AssetDisposal;
use App\Models\AssetActivity;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class IllegalAssetResource extends Resource
{
    protected static ?string $model = IllegalAsset::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    protected static string|\UnitEnum|null $navigationGroup = 'Asset Management';
    protected static ?string $navigationLabel = 'Illegal Assets';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Asset Registration Details')
                    ->schema([
                        Forms\Components\TextInput::make('asset_type')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(65535),
                        Forms\Components\TextInput::make('owner_name')
                            ->label('Owner Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('owner_phone')
                            ->label('Owner Phone')
                            ->tel()
                            ->maxLength(20),
                        \Filament\Schemas\Components\Section::make('Location Found')
                            ->schema([
                                Forms\Components\Select::make('sub_city_id')
                                    ->label('Sub City (ክፍለ ከተማ)')
                                    ->options(\App\Models\SubCity::all()->pluck('name_am', 'id'))
                                    ->required()
                                    ->live(),
                                Forms\Components\Select::make('woreda_id')
                                    ->label('Woreda (ወረዳ)')
                                    ->options(function (callable $get) {
                                        $subCityId = $get('sub_city_id');
                                        if ($subCityId) {
                                            return \App\Models\Woreda::where('sub_city_id', $subCityId)
                                                ->pluck('name_am', 'id');
                                        }
                                        return [];
                                    })
                                    ->required(),
                                Forms\Components\TextInput::make('kebele')
                                    ->label('Kebele (ቀበሌ)')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('house_number')
                                    ->label('House Number (የቤት ቁጥር)')
                                    ->maxLength(255),
                            ])->columns(2),
                        Forms\Components\DatePicker::make('date_confiscated')
                            ->required(),
                        Forms\Components\Select::make('officer_id')
                            ->relationship('officer', 'badge_number')
                            ->searchable()
                            ->preload()
                            ->required(),
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
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_confiscated')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner_name')
                    ->label('Owner Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subCity.name_am')
                    ->label('Sub City')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('woreda.name_am')
                    ->label('Woreda')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('officer.badge_number')
                    ->label('Officer Badge')
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name_en')
                    ->label('Department')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Registered' => 'gray',
                        'Handed Over' => 'info',
                        'Estimated' => 'warning',
                        'Transferred' => 'primary',
                        'Sold' => 'success',
                        'Disposed' => 'danger',
                        default => 'gray',
                    }),
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
                
                Action::make('print_history')
                    ->label('Print History')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn(IllegalAsset $record) => route('admin.illegal-assets.print-history', $record->id))
                    ->openUrlInNewTab(),
                
                // Asset Handover Modal Action
                Action::make('handover')
                    ->label('Hand Over')
                    ->icon('heroicon-o-hand-raised')
                    ->color('warning')
                    ->visible(fn ($record) => in_array($record->status, ['Registered', 'Handed Over', 'Estimated']) && Auth::user()->can('handover', $record))
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
                    ->action(function (array $data, IllegalAsset $record): void {
                        AssetHandover::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Handed Over']);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Handed Over',
                            'description' => "Asset handed over to department ID: {$data['department_id']}",
                        ]);
                    }),

                // Asset Estimation Modal Action
                Action::make('estimate')
                    ->label('Estimate')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->visible(fn ($record) => in_array($record->status, ['Registered', 'Handed Over']) && Auth::user()->can('estimate', $record))
                    ->form([
                        Forms\Components\TextInput::make('estimated_value')
                            ->numeric()->prefix('ETB')->required(),
                        Forms\Components\TextInput::make('evaluator_name')->required(),
                        Forms\Components\DatePicker::make('evaluation_date')->default(now())->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (array $data, IllegalAsset $record): void {
                        AssetEstimation::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Estimated']);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Estimated',
                            'description' => "Asset estimated value: {$data['estimated_value']} ETB by {$data['evaluator_name']}",
                        ]);
                    }),

                // Asset Transfer Modal Action
                Action::make('transfer')
                    ->label('Transfer')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('gray')
                    ->visible(fn ($record) => in_array($record->status, ['Handed Over', 'Estimated']) && Auth::user()->can('transfer', $record))
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
                    ->action(function (array $data, IllegalAsset $record): void {
                        AssetTransfer::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Transferred']);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Transferred',
                            'description' => "Asset transferred from department ID: {$data['from_department_id']} to {$data['to_department_id']}",
                        ]);
                    }),

                // Asset Sale Modal Action
                Action::make('sell')
                    ->label('Sell')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['Registered', 'Handed Over', 'Estimated', 'Transferred']) && Auth::user()->can('sell', $record))
                    ->form([
                        Forms\Components\TextInput::make('buyer_name')->required(),
                        Forms\Components\TextInput::make('buyer_contact'),
                        Forms\Components\TextInput::make('sale_price')->numeric()->prefix('ETB')->required(),
                        Forms\Components\Select::make('sold_by_officer_id')
                            ->options(Officer::pluck('badge_number', 'id')->toArray())
                            ->label('Sold By')->required(),
                        Forms\Components\DatePicker::make('sale_date')->default(now())->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (array $data, IllegalAsset $record): void {
                        AssetSale::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Sold']);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Sold',
                            'description' => "Asset sold to {$data['buyer_name']} for {$data['sale_price']} ETB",
                        ]);
                    }),

                // Asset Disposal Modal Action
                Action::make('dispose')
                    ->label('Dispose')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn ($record) => !in_array($record->status, ['Sold', 'Disposed']) && Auth::user()->can('dispose', $record))
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
                    ->action(function (array $data, IllegalAsset $record): void {
                        AssetDisposal::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Disposed']);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Disposed',
                            'description' => "Asset disposed via {$data['disposal_method']}",
                        ]);
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
            ActivitiesRelationManager::class,
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
