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
        $user = Auth::user();

        // Resolve the officer's sub_city and woreda IDs from the user's string fields
        $defaultSubCityId = null;
        $defaultWoredaId = null;
        $locationAutoFilled = false;

        if ($user && $user->sub_city) {
            $subCity = \App\Models\SubCity::where('name_en', $user->sub_city)->first();
            if ($subCity) {
                $defaultSubCityId = $subCity->id;
                $locationAutoFilled = true;

                if ($user->woreda) {
                    $woredaName = 'Woreda ' . str_pad($user->woreda, 2, '0', STR_PAD_LEFT);
                    $woreda = \App\Models\Woreda::where('sub_city_id', $subCity->id)
                        ->where('name_en', $woredaName)
                        ->first();
                    if ($woreda) {
                        $defaultWoredaId = $woreda->id;
                    }
                }
            }
        }

        // Find the officer record linked to the logged-in user
        $officerId = null;
        if ($user) {
            $officer = Officer::where('user_id', $user->id)->first();
            $officerId = $officer?->id;
        }

        return $schema
            ->schema([
                // Section 1: Owner Information
                \Filament\Schemas\Components\Section::make('Owner Information')
                    ->schema([
                        Forms\Components\TextInput::make('owner_name')
                            ->label('Owner Name (የባለቤት ስም)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('owner_phone')
                            ->label('Owner Phone (ስልክ ቁጥር)')
                            ->tel()
                            ->maxLength(20),
                    ])->columns(2),

                // Section 2: Location (auto-filled from officer's assignment)
                \Filament\Schemas\Components\Section::make('Location Found (የተገኘበት ቦታ)')
                    ->schema([
                        Forms\Components\Select::make('sub_city_id')
                            ->label('Sub City (ክፍለ ከተማ)')
                            ->options(\App\Models\SubCity::all()->pluck('name_am', 'id'))
                            ->default($defaultSubCityId)
                            ->disabled($locationAutoFilled)
                            ->dehydrated()
                            ->required()
                            ->hidden(fn(string $operation): bool => $operation === 'create' && $locationAutoFilled)
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
                            ->default($defaultWoredaId)
                            ->disabled($locationAutoFilled && $defaultWoredaId)
                            ->dehydrated()
                            ->required()
                            ->hidden(fn(string $operation): bool => $operation === 'create' && $locationAutoFilled),
                        Forms\Components\TextInput::make('kebele')
                            ->label('Kebele (ቀበሌ)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('house_number')
                            ->label('House Number (የቤት ቁጥር)')
                            ->maxLength(255),
                    ])->columns(2),

                // Section 3: Confiscated Items
                \Filament\Schemas\Components\Section::make('Confiscated Items (የተያዙ ዕቃዎች)')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->schema([
                                Forms\Components\TextInput::make('asset_type')
                                    ->label('Item Type (ዓይነት)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantity (ብዛት)')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description (ገለጻ)')
                                    ->required()
                                    ->maxLength(65535),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('Add Another Item')
                            ->columnSpanFull()
                            ->visible(fn (string $operation): bool => $operation === 'create'),

                        // For edit mode, show single item fields directly
                        Forms\Components\TextInput::make('asset_type')
                            ->label('Item Type (ዓይነት)')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity (ብዛት)')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                        Forms\Components\Textarea::make('description')
                            ->label('Description (ገለጻ)')
                            ->required()
                            ->maxLength(65535)
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ]),

                // Hidden / Auto-set fields
                \Filament\Schemas\Components\Section::make('Registration Details')
                    ->schema([
                        Forms\Components\DatePicker::make('date_confiscated')
                            ->default(now())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\Select::make('officer_id')
                            ->relationship('officer', 'badge_number')
                            ->default($officerId)
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
                            ->default('Registered')
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ])->columns(2)
                    ->visible(fn (string $operation): bool => $operation === 'edit'),

                \Filament\Schemas\Components\Section::make('Attachments (አባሪዎች)')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->directory('illegal-asset-attachments')
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner_name')
                    ->label('Owner Name (የባለቤት ስም)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_confiscated')
                    ->label('Date (ቀን)')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subCity.name_am')
                    ->label('Sub City (ክፍለ ከተማ)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        IllegalAsset::STATUS_REGISTERED => 'gray',
                        IllegalAsset::STATUS_HANDOVER_PENDING => 'warning',
                        IllegalAsset::STATUS_HANDED_OVER => 'info',
                        IllegalAsset::STATUS_HANDOVER_REJECTED => 'danger',
                        IllegalAsset::STATUS_TRANSFER_PENDING => 'warning',
                        IllegalAsset::STATUS_TRANSFERRED => 'success',
                        IllegalAsset::STATUS_TRANSFER_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        IllegalAsset::STATUS_REGISTERED => 'heroicon-m-pencil-square',
                        IllegalAsset::STATUS_HANDOVER_PENDING => 'heroicon-m-arrow-path',
                        IllegalAsset::STATUS_HANDED_OVER => 'heroicon-m-check-circle',
                        IllegalAsset::STATUS_HANDOVER_REJECTED => 'heroicon-m-x-circle',
                        IllegalAsset::STATUS_TRANSFER_PENDING => 'heroicon-m-truck',
                        IllegalAsset::STATUS_TRANSFERRED => 'heroicon-m-flag',
                        IllegalAsset::STATUS_TRANSFER_REJECTED => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    }),
                Tables\Columns\TextColumn::make('next_step')
                    ->label('Next Action (ቀጣይ ተግባር)')
                    ->getStateUsing(function (IllegalAsset $record): string {
                        $user = Auth::user();
                        if (!$user) return 'N/A';

                        return match (true) {
                            $record->status === IllegalAsset::STATUS_REGISTERED && $user->hasRole('officer') => 'Request Handover',
                            $record->status === IllegalAsset::STATUS_HANDOVER_PENDING && $user->hasRole('woreda_officer') => 'Review Handover',
                            $record->status === IllegalAsset::STATUS_HANDED_OVER && $user->hasRole('woreda_officer') => 'Request Transfer',
                            $record->status === IllegalAsset::STATUS_TRANSFER_PENDING && $user->hasRole('sub_city_officer') => 'Review Transfer',
                            $record->status === IllegalAsset::STATUS_TRANSFERRED => 'Awaiting Exit (Sale/Disposal)',
                            default => 'No pending action',
                        };
                    })
                    ->badge()
                    ->color('primary'),
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
            ])
            ->actions([
                
                Action::make('print_history')
                    ->label('History')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->modalHeading('Asset Lifecycle History')
                    ->modalContent(fn (IllegalAsset $record) => view('admin.illegal-assets.history-modal', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->extraModalFooterActions([
                        Action::make('print_report')
                            ->label('Print Report')
                            ->icon('heroicon-o-printer')
                            ->color('primary')
                            ->url(fn (IllegalAsset $record) => route('admin.illegal-assets.print-history', $record->id))
                            ->openUrlInNewTab(),
                    ]),
                
                // 1. Request Handover (Officer -> Woreda)
                Action::make('requestHandover')
                    ->label('Request Handover')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === IllegalAsset::STATUS_REGISTERED && Auth::user()->hasRole(['officer', 'admin']))
                    ->form([
                        Forms\Components\Select::make('to_woreda_id')
                            ->label('Target Woreda')
                            ->options(\App\Models\Woreda::pluck('name_am', 'id'))
                            ->default(fn($record) => $record->woreda_id)
                            ->required(),
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('handover-requests')
                            ->required()
                            ->label('Evidence/Documents'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Additional Notes'),
                    ])
                    ->action(function (array $data, IllegalAsset $record): void {
                        AssetHandover::create([
                            'illegal_asset_id' => $record->id,
                            'to_woreda_id' => $data['to_woreda_id'],
                            'notes' => $data['notes'],
                            'attachments' => $data['attachments'],
                            'handover_date' => now(),
                            'confirmation_status' => 'Pending',
                        ]);
                        $record->update(['status' => IllegalAsset::STATUS_HANDOVER_PENDING]);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Handover Requested',
                            'description' => "Officer requested handover to Woreda ID: {$data['to_woreda_id']}",
                        ]);
                    }),

                // 2. Review Handover (Woreda Admin)
                Action::make('reviewHandover')
                    ->label('Review Handover')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === IllegalAsset::STATUS_HANDOVER_PENDING && Auth::user()->hasRole(['woreda_officer', 'admin']))
                    ->form([
                        Forms\Components\Radio::make('decision')
                            ->options([
                                'approve' => 'Approve',
                                'reject' => 'Reject',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required(fn($get) => $get('decision') === 'reject')
                            ->visible(fn($get) => $get('decision') === 'reject'),
                        Forms\Components\Select::make('handed_over_to_officer_id')
                            ->label('Received By Officer')
                            ->options(Officer::pluck('badge_number', 'id'))
                            ->required(fn($get) => $get('decision') === 'approve')
                            ->visible(fn($get) => $get('decision') === 'approve'),
                        Forms\Components\FileUpload::make('approval_docs')
                            ->label('Approval Documents')
                            ->directory('handover-approvals')
                            ->visible(fn($get) => $get('decision') === 'approve'),
                    ])
                    ->action(function (array $data, IllegalAsset $record): void {
                        $handover = $record->handovers()->where('confirmation_status', 'Pending')->latest()->first();
                        
                        if ($data['decision'] === 'approve') {
                            $handover?->update([
                                'confirmation_status' => 'Approved',
                                'confirmed_by_user_id' => Auth::id(),
                                'confirmed_at' => now(),
                                'handed_over_to_officer_id' => $data['handed_over_to_officer_id'],
                            ]);
                            $record->update(['status' => IllegalAsset::STATUS_HANDED_OVER]);
                            
                            AssetActivity::create([
                                'illegal_asset_id' => $record->id,
                                'user_id' => Auth::id(),
                                'action' => 'Handover Approved',
                                'description' => "Woreda Admin approved handover.",
                            ]);
                        } else {
                            $handover?->update([
                                'confirmation_status' => 'Rejected',
                                'rejection_reason' => $data['rejection_reason'],
                            ]);
                            $record->update(['status' => IllegalAsset::STATUS_HANDOVER_REJECTED]);
                            
                            AssetActivity::create([
                                'illegal_asset_id' => $record->id,
                                'user_id' => Auth::id(),
                                'action' => 'Handover Rejected',
                                'description' => "Reason: " . $data['rejection_reason'],
                            ]);
                        }
                    }),

                // 3. Request Transfer (Woreda Admin -> Sub-City)
                Action::make('requestTransfer')
                    ->label('Request Transfer to Sub-City')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === IllegalAsset::STATUS_HANDED_OVER && Auth::user()->hasRole(['woreda_officer', 'admin']))
                    ->form([
                        Forms\Components\Select::make('to_sub_city_id')
                            ->label('Target Sub-City')
                            ->options(\App\Models\SubCity::pluck('name_am', 'id'))
                            ->default(fn($record) => $record->sub_city_id)
                            ->required(),
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('transfer-requests')
                            ->required()
                            ->label('Transfer Documents/Images'),
                        Forms\Components\TextInput::make('from_storage_facility')->label('From Facility'),
                        Forms\Components\TextInput::make('to_storage_facility')->label('To Facility'),
                        Forms\Components\Textarea::make('notes')->label('Notes'),
                    ])
                    ->action(function (array $data, IllegalAsset $record): void {
                        AssetTransfer::create([
                            'illegal_asset_id' => $record->id,
                            'from_woreda_id' => $record->woreda_id,
                            'to_sub_city_id' => $data['to_sub_city_id'],
                            'from_storage_facility' => $data['from_storage_facility'],
                            'to_storage_facility' => $data['to_storage_facility'],
                            'notes' => $data['notes'],
                            'attachments' => $data['attachments'],
                            'transfer_date' => now(),
                            'confirmation_status' => 'Pending',
                        ]);
                        $record->update(['status' => IllegalAsset::STATUS_TRANSFER_PENDING]);
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Transfer Requested',
                            'description' => "Woreda requested transfer to Sub-City ID: {$data['to_sub_city_id']}",
                        ]);
                    }),

                // 4. Review Transfer (Sub-City Admin)
                Action::make('reviewTransfer')
                    ->label('Review Transfer')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === IllegalAsset::STATUS_TRANSFER_PENDING && Auth::user()->hasRole(['sub_city_officer', 'admin']))
                    ->form([
                        Forms\Components\Radio::make('decision')
                            ->options([
                                'approve' => 'Approve',
                                'reject' => 'Reject',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required(fn($get) => $get('decision') === 'reject')
                            ->visible(fn($get) => $get('decision') === 'reject'),
                        Forms\Components\FileUpload::make('receipt_proof')
                            ->label('Receipt Proof')
                            ->directory('transfer-receipts')
                            ->visible(fn($get) => $get('decision') === 'approve'),
                    ])
                    ->action(function (array $data, IllegalAsset $record): void {
                        $transfer = $record->transfers()->where('confirmation_status', 'Pending')->latest()->first();
                        
                        if ($data['decision'] === 'approve') {
                            $transfer?->update([
                                'confirmation_status' => 'Approved',
                                'confirmed_by_user_id' => Auth::id(),
                                'confirmed_at' => now(),
                            ]);
                            $record->update(['status' => IllegalAsset::STATUS_TRANSFERRED]);
                            
                            AssetActivity::create([
                                'illegal_asset_id' => $record->id,
                                'user_id' => Auth::id(),
                                'action' => 'Transfer Approved',
                                'description' => "Sub-City Admin approved transfer.",
                            ]);
                        } else {
                            $transfer?->update([
                                'confirmation_status' => 'Rejected',
                                'rejection_reason' => $data['rejection_reason'],
                            ]);
                            $record->update(['status' => IllegalAsset::STATUS_TRANSFER_REJECTED]);
                            
                            AssetActivity::create([
                                'illegal_asset_id' => $record->id,
                                'user_id' => Auth::id(),
                                'action' => 'Transfer Rejected',
                                'description' => "Reason: " . $data['rejection_reason'],
                            ]);
                        }
                    }),

                // 5. Asset Estimation
                Action::make('estimate')
                    ->label('Estimate Value')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->visible(fn ($record) => in_array($record->status, [IllegalAsset::STATUS_REGISTERED, IllegalAsset::STATUS_HANDED_OVER, IllegalAsset::STATUS_TRANSFERRED]) && Auth::user()->hasRole(['admin', 'officer', 'woreda_officer']))
                    ->form([
                        Forms\Components\TextInput::make('estimated_value')
                            ->numeric()->prefix('ETB')->required(),
                        Forms\Components\TextInput::make('evaluator_name')->required(),
                        Forms\Components\DatePicker::make('evaluation_date')->default(now())->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (array $data, IllegalAsset $record): void {
                        AssetEstimation::create(array_merge($data, ['illegal_asset_id' => $record->id]));
                        
                        AssetActivity::create([
                            'illegal_asset_id' => $record->id,
                            'user_id' => Auth::id(),
                            'action' => 'Estimated',
                            'description' => "Asset estimated value: {$data['estimated_value']} ETB by {$data['evaluator_name']}",
                        ]);
                    }),

                // 6. Asset Sale
                Action::make('sell')
                    ->label('Process Sale')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === IllegalAsset::STATUS_TRANSFERRED && Auth::user()->hasRole(['admin', 'sub_city_officer']))
                    ->form([
                        Forms\Components\TextInput::make('buyer_name')->required(),
                        Forms\Components\TextInput::make('buyer_contact'),
                        Forms\Components\TextInput::make('sale_price')->numeric()->prefix('ETB')->required(),
                        Forms\Components\Select::make('sold_by_officer_id')
                            ->options(Officer::pluck('badge_number', 'id')->toArray())
                            ->label('Sold By')->required(),
                        Forms\Components\DatePicker::make('sale_date')->default(now())->required(),
                        Forms\Components\Textarea::make('notes'),
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('sale-attachments'),
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

                // 7. Asset Disposal
                Action::make('dispose')
                    ->label('Dispose Asset')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === IllegalAsset::STATUS_TRANSFERRED && Auth::user()->hasRole(['admin', 'sub_city_officer']))
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
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('disposal-attachments'),
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
