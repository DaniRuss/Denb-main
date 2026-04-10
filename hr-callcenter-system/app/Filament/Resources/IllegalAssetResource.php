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
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->directory('illegal-asset-attachments')
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->columnSpanFull(),
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
                    ->formatStateUsing(fn (string $state): string => str_replace('Handover Pending Confirmation', 'Transfer Pending Confirmation', $state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Registered' => 'gray',
                        'Handover Pending Confirmation' => 'info',
                        'Handed Over' => 'info',
                        'Estimated' => 'warning',
                        'Transfer Pending Confirmation' => 'warning',
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
                        'Handover Pending Confirmation' => 'Handover Pending Confirmation',
                        'Handed Over' => 'Handed Over',
                        'Estimated' => 'Estimated',
                        'Transfer Pending Confirmation' => 'Transfer Pending Confirmation',
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
                    ->label('Transfer')
                    ->icon('heroicon-o-hand-raised')
                    ->color('warning')
                    ->visible(fn ($record) => in_array($record->status, ['Registered', 'Handed Over', 'Estimated']) && Auth::user()->can('handover', $record))
                    ->form([
                        Forms\Components\Select::make('department_id')
                            ->label('To Department (Optional fallback)')
                            ->options(Department::pluck('name_en', 'id')->toArray()),
                        Forms\Components\Select::make('to_woreda_id')
                            ->label('To Woreda')
                            ->options(\App\Models\Woreda::pluck('name_am', 'id')->toArray())
                            ->required(),
                        Forms\Components\Select::make('handed_over_to_officer_id')
                            ->label('To Officer')
                            ->options(Officer::pluck('badge_number', 'id')->toArray())
                            ->required(),
                        Forms\Components\DatePicker::make('handover_date')
                            ->default(now())->disabled()->dehydrated()->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (array $data, IllegalAsset $record): void {
                        $data['confirmation_status'] = 'Pending';
                        AssetHandover::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Handover Pending Confirmation']);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Handover Pending',
                            'description' => "Asset handover initiated to Woreda ID: {$data['to_woreda_id']}",
                        ]);
                    }),

                // Asset Handover Confirmation Action
                Action::make('confirm_handover')
                    ->label('Confirm Transfer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'Handover Pending Confirmation')
                    ->action(function (IllegalAsset $record): void {
                        $handover = AssetHandover::where('illegal_asset_id', $record->id)->latest()->first();
                        if ($handover) {
                            $handover->update([
                                'confirmation_status' => 'Confirmed',
                                'confirmed_by_user_id' => Auth::id(),
                                'confirmed_at' => now(),
                            ]);
                        }
                        $record->update(['status' => 'Handed Over']);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Handover Confirmed',
                            'description' => "Asset handover confirmed by Woreda",
                        ]);
                    }),

                // Asset Estimation Modal Action
                Action::make('estimate')
                    ->label('Estimate')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->visible(fn ($record) => in_array($record->status, ['Registered', 'Handed Over', 'Handover Pending Confirmation']) && Auth::user()->can('estimate', $record))
                    ->form([
                        Forms\Components\TextInput::make('estimated_value')
                            ->numeric()->prefix('ETB')->required(),
                        Forms\Components\TextInput::make('evaluator_name')->required(),
                        Forms\Components\DatePicker::make('evaluation_date')->default(now())->disabled()->dehydrated()->required(),
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
                    ->visible(fn ($record) => in_array($record->status, ['Registered', 'Handed Over', 'Estimated', 'Handover Pending Confirmation']) && Auth::user()->can('transfer', $record))
                    ->fillForm(fn (IllegalAsset $record): array => [
                        'from_woreda_id' => $record->woreda_id,
                        'to_sub_city_id' => $record->sub_city_id,
                    ])
                    ->form([
                        Forms\Components\Select::make('from_woreda_id')
                            ->label('From Woreda')
                            ->options(\App\Models\Woreda::pluck('name_am', 'id')->toArray())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\Select::make('to_sub_city_id')
                            ->label('To SubCity')
                            ->options(\App\Models\SubCity::pluck('name_am', 'id')->toArray())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\TextInput::make('from_storage_facility')->label('From Facility'),
                        Forms\Components\TextInput::make('to_storage_facility')->label('To Facility'),
                        Forms\Components\Select::make('transferred_by_officer_id')
                            ->options(Officer::pluck('badge_number', 'id')->toArray())
                            ->label('Transferred By')->required(),
                        Forms\Components\DatePicker::make('transfer_date')->default(now())->disabled()->dehydrated()->required(),
                        Forms\Components\Textarea::make('notes'),
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->directory('transfer-attachments')
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/*', 'application/pdf']),
                    ])
                    ->action(function (array $data, IllegalAsset $record): void {
                        $data['confirmation_status'] = 'Pending';
                        AssetTransfer::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        $record->update(['status' => 'Transfer Pending Confirmation']);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Transfer Pending',
                            'description' => "Asset transfer initiated from Woreda ID: {$data['from_woreda_id']} to SubCity ID: {$data['to_sub_city_id']}",
                        ]);
                    }),

                // Asset Transfer Confirmation Action
                Action::make('confirm_transfer')
                    ->label('Confirm Transfer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'Transfer Pending Confirmation')
                    ->action(function (IllegalAsset $record): void {
                        $transfer = $record->transfers()->latest()->first();
                        if ($transfer) {
                            $transfer->update([
                                'confirmation_status' => 'Confirmed',
                                'confirmed_by_user_id' => Auth::id(),
                                'confirmed_at' => now(),
                            ]);
                        }
                        $record->update(['status' => 'Transferred']);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Transfer Confirmed',
                            'description' => "Asset transfer confirmed by SubCity",
                        ]);
                    }),

                // Asset Sale Modal Action
                Action::make('sell')
                    ->label('Sell')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['Registered', 'Handed Over', 'Estimated', 'Transferred', 'Transfer Pending Confirmation']) && Auth::user()->can('sell', $record))
                    ->form([
                        Forms\Components\TextInput::make('buyer_name')->required(),
                        Forms\Components\TextInput::make('buyer_contact'),
                        Forms\Components\TextInput::make('sale_price')->numeric()->prefix('ETB')->required(),
                        Forms\Components\Select::make('sold_by_officer_id')
                            ->options(Officer::pluck('badge_number', 'id')->toArray())
                            ->label('Sold By')->required(),
                        Forms\Components\DatePicker::make('sale_date')->default(now())->disabled()->dehydrated()->required(),
                        Forms\Components\Textarea::make('notes'),
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->directory('sale-attachments')
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/*', 'application/pdf']),
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
                        Forms\Components\DatePicker::make('disposal_date')->default(now())->disabled()->dehydrated()->required(),
                        Forms\Components\Textarea::make('notes'),
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->directory('disposal-attachments')
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/*', 'application/pdf']),
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
            // HandoversRelationManager::class,
            // EstimationsRelationManager::class,
            // TransfersRelationManager::class,
            // SalesRelationManager::class,
            // DisposalsRelationManager::class,
            // ActivitiesRelationManager::class,
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
