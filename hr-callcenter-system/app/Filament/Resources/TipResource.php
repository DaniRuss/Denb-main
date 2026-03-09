<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipResource\Pages;
use App\Models\Tip;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Models\User;

class TipResource extends Resource
{
    protected static ?string $model = Tip::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-flag';
    protected static string|\UnitEnum|null $navigationGroup = 'Case Management';
    protected static ?string $navigationLabel = 'Public Tips';
    protected static ?string $pluralLabel = 'Public Tips';
    protected static ?string $modelLabel = 'Tip';
    protected static ?int $navigationSort = 2;

    // NO FORM - Admins don't create tips!
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Empty - prevent tip creation in admin
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tip_number')
                    ->label('Tip #')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tip_type')
                    ->label('Type')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'illegal_trade' => 'Illegal Trade',
                        'alcohol_sales' => 'Alcohol',
                        'land_grabbing' => 'Land',
                        'drug_activity' => 'Drugs',
                        'counterfeit_goods' => 'Counterfeit',
                        'illegal_construction' => 'Construction',
                        'environmental_violation' => 'Environment',
                        'other' => 'Other',
                        default => $state,
                    })
                    ->badge(),

                Tables\Columns\TextColumn::make('urgency_level')
                    ->label('Urgency')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        'low' => 'secondary',
                        'medium' => 'info',
                        'high' => 'warning',
                        'immediate' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        'pending' => 'secondary',
                        'investigating' => 'info',
                        'verified' => 'success',
                        'false_report' => 'danger',
                        'action_taken' => 'success',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Investigator')
                    ->default('Unassigned'),

                Tables\Columns\IconColumn::make('is_ongoing')
                    ->label('Ongoing')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('tip_type'),
                Tables\Filters\Filter::make('is_ongoing')
                    ->label('Ongoing Activities')
                    ->query(fn($query) => $query->where('is_ongoing', true))
                    ->toggle(),
            ])
            ->actions([
                ViewAction::make()
                    ->label('View Details')
                    ->icon('heroicon-o-eye'),

                Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Select::make('assigned_to')
                            ->options(User::pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (Tip $record, array $data) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'status' => 'investigating',
                        ]);

                        Notification::make()
                            ->title('Tip assigned')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Tip $record) => $record->status === 'pending'),

                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(function (Tip $record) {
                        $record->update(['status' => 'verified']);

                        Notification::make()
                            ->title('Tip verified')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Tip $record) => $record->status === 'investigating'),

                // Plan Operation
                Action::make('plan_operation')
                    ->label('Plan Operation')
                    ->icon('heroicon-o-map')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('operation_name')
                            ->label('Operation Name')
                            ->required(),
                        \Filament\Forms\Components\DateTimePicker::make('planned_date')
                            ->label('Planned Date/Time')
                            ->required(),
                        \Filament\Forms\Components\Select::make('team_size')
                            ->options([2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10])
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('plan_details')
                            ->label('Operation Plan')
                            ->required()
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Select::make('resources_needed')
                            ->options([
                                'vehicle' => 'Vehicle',
                                'weapons' => 'Weapons',
                                'warrant' => 'Search Warrant',
                                'backup' => 'Backup Team',
                                'k9' => 'K-9 Unit',
                            ])
                            ->multiple(),
                    ])
                    ->action(function (Tip $record, array $data) {
                        $record->update(['status' => 'action_taken']);

                        Notification::make()
                            ->title('Operation planned')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Tip $record) => $record->status === 'verified' && $record->urgency_level === 'immediate'),

                // Add to Watchlist
                Action::make('add_watchlist')
                    ->label('Add to Watchlist')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('priority')
                            ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
                            ->required(),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Monitor until'),
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label('Monitoring instructions'),
                    ])
                    ->action(function (Tip $record, array $data) {
                        Notification::make()
                            ->title('Location added to watchlist')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Tip $record) => $record->status === 'verified' && $record->is_ongoing),

                // Coordinate with Partner
                Action::make('coordinate')
                    ->label('Share with Partner')
                    ->icon('heroicon-o-share')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\Select::make('agency')
                            ->options([
                                'police' => 'Federal Police',
                                'revenue' => 'Revenue Authority',
                                'immigration' => 'Immigration',
                                'customs' => 'Customs',
                                'interpol' => 'INTERPOL',
                            ])
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('message')
                            ->label('Message to partner agency')
                            ->required(),
                        \Filament\Forms\Components\Checkbox::make('anonymize')
                            ->label('Remove reporter info')
                            ->default(true),
                    ])
                    ->action(function (Tip $record, array $data) {
                        Notification::make()
                            ->title('Shared with ' . $data['agency'])
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Tip $record) => $record->status === 'verified'),

                // Send Alert to Field Officers
                Action::make('send_alert')
                    ->label('Send Alert to Field')
                    ->icon('heroicon-o-bell')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Select::make('area')
                            ->options([
                                'all' => 'All Officers',
                                'subcity' => 'This Sub-city Only',
                                'nearby' => 'Nearby Units',
                            ])
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('instructions')
                            ->label('Instructions')
                            ->required(),
                    ])
                    ->action(function (Tip $record, array $data) {
                        Notification::make()
                            ->title('Alert sent to field officers')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Tip $record) => $record->urgency_level === 'immediate' && $record->is_ongoing),

                // Add Intelligence Value Rating
                Action::make('rate_value')
                    ->label('Rate Intelligence Value')
                    ->icon('heroicon-o-chart-bar')
                    ->color('gray')
                    ->form([
                        \Filament\Forms\Components\Select::make('value')
                            ->options([
                                1 => '1 - Low Value',
                                2 => '2 - Some Value',
                                3 => '3 - Medium Value',
                                4 => '4 - High Value',
                                5 => '5 - Critical Value',
                            ])
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('justification')
                            ->label('Justification'),
                    ])
                    ->action(function (Tip $record, array $data) {
                        $record->update(['intelligence_value' => $data['value']]);

                        Notification::make()
                            ->title('Intelligence value rated')
                            ->success()
                            ->send();
                    }),

                // Generate QR Code for Tracking (for anonymous tips)
                Action::make('generate_qr')
                    ->label('Generate QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('gray')
                    ->action(function (Tip $record) {
                        Notification::make()
                            ->title('QR Code generated')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Tip $record) => $record->is_anonymous),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Tip Information')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(3)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('tip_number')
                                    ->label('Tip Number')
                                    ->copyable()
                                    ->weight('bold'),
                                \Filament\Infolists\Components\TextEntry::make('created_at')
                                    ->label('Received')
                                    ->dateTime(),
                                \Filament\Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),
                            ]),
                    ]),

                \Filament\Schemas\Components\Section::make('Details')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('tip_type')
                                    ->label('Type'),
                                \Filament\Infolists\Components\TextEntry::make('location')
                                    ->label('Location'),
                            ]),
                        \Filament\Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->markdown(),
                        \Filament\Infolists\Components\TextEntry::make('suspect_description')
                            ->label('Suspect Details')
                            ->columnSpanFull(),
                    ]),

                \Filament\Schemas\Components\Section::make('Evidence & Attachments')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('evidence_description')
                            ->label('Description of Evidence')
                            ->placeholder('No description provided'),
                        \Filament\Infolists\Components\TextEntry::make('evidence_files')
                            ->label('Attached Files')
                            ->badge()
                            ->separator(',')
                            ->formatStateUsing(fn($state) => basename($state))
                            ->url(fn($state) => asset('storage/' . $state), true)
                            ->placeholder('No attachments provided'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTips::route('/'),
            'view' => Pages\ViewTip::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}
