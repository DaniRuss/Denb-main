<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VolunteerTipResource\Pages;
use App\Models\AwarenessEngagement;
use App\Models\VolunteerTip;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class VolunteerTipResource extends Resource
{
    protected static ?string $model = VolunteerTip::class;

    public static function canCreate(): bool
    {
        return auth()->user() && auth()->user()->can('submit_tips');
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';
    protected static string|\UnitEnum|null $navigationGroup  = 'Awareness Management';
    protected static ?string $navigationLabel = 'Volunteer Tips | ጥቆማ';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('መሠረታዊ መረጃ - Base Information')
                    ->description('Details about the suspected violation and linked awareness activity.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('engagement_id')
                                    ->relationship('engagement', 'engagement_code')
                                    ->label('Linked Engagement / የተያያዘ ምዝገባ')
                                    ->searchable()
                                    ->placeholder('optional')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $engagement = \App\Models\AwarenessEngagement::find($state);
                                            if ($engagement) {
                                                $set('sub_city_id', $engagement->sub_city_id);
                                                $set('woreda_id', $engagement->woreda_id);
                                                $set('block_number', $engagement->block_number);
                                                if ($engagement->violation_type) {
                                                    $set('violation_type', $engagement->violation_type);
                                                }
                                            }
                                        }
                                    }),
                                Forms\Components\Select::make('violation_type')
                                    ->label('Violation Type (የተፈጸመው ህገወጥ ተግባር አይነት)')
                                    ->options(AwarenessEngagement::violationLabels())
                                    ->required(),
                                Forms\Components\TextInput::make('suspect_name')
                                    ->label('Suspect Name / የተጠርጣሪው ስም')
                                    ->placeholder('Enter name if known'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('violation_date')
                                    ->label('Date of the Act (የተፈጸመበት ቀን)')
                                    ->required(),
                                Forms\Components\DatePicker::make('reported_date')
                                    ->label('Date Tip Received (መረጃው የመጣበት ቀን)')
                                    ->default(now())
                                    ->required(),
                            ]),
                    ]),

                Section::make('የቦታ ዝርዝር - Location Details')
                    ->description('Precise location of the code violation.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('sub_city_id')
                                    ->label('Sub-City (ክፍለ ከተማ)')
                                    ->options(\App\Models\SubCity::orderBy('name_am')->pluck('name_am', 'id')->toArray())
                                    ->required()
                                    ->live()
                                    ->searchable()
                                    ->placeholder('Select Sub-City')
                                    ->afterStateUpdated(fn (Set $set) => $set('woreda_id', null)),
                                Forms\Components\Select::make('woreda_id')
                                    ->label('Woreda (ወረዳ)')
                                    ->options(function (callable $get) {
                                        $subCityId = $get('sub_city_id');
                                        if ($subCityId) {
                                            return \App\Models\Woreda::where('sub_city_id', $subCityId)
                                                ->orderBy('name_am')
                                                ->pluck('name_am', 'id')
                                                ->toArray();
                                        }
                                        return [];
                                    })
                                    ->required()
                                    ->live()
                                    ->searchable()
                                    ->placeholder('Select Woreda'),
                                Forms\Components\TextInput::make('block_number')
                                    ->label('Block No. / ብሎክ ቁጥር'),
                            ]),
                        Forms\Components\Textarea::make('violation_location')
                            ->label('Location/Block (የተፈጸመት ልዩ ቦታ/ቀጣነው/ብሎክ)')
                            ->required()
                            ->placeholder('Describe the exact spot'),
                    ]),

                Section::make('የጠቋሚው መረጃና ማስረጃ - Source & Evidence')
                    ->description('Verification source and supporting documents.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_anonymous')
                                    ->label('Anonymous Tip? (በምስጢር የቀረበ ጥቆማ?)')
                                    ->live()
                                    ->default(false),
                                Forms\Components\FileUpload::make('volunteer_signature_path')
                                    ->label('Signature/Evidence (ፊርማ ወይም ማስረጃ)')
                                    ->placeholder('Click or drag to upload'),
                            ]),
                        
                        Forms\Components\TextInput::make('volunteer_name')
                            ->label('Volunteer Name (መረጃው ያመጣው በጎ ፈቃድ ስም)')
                            ->hidden(fn (callable $get) => $get('is_anonymous'))
                            ->placeholder('Enter name if not anonymous'),
                    ]),

                Forms\Components\Hidden::make('status')->default('draft'),
                Forms\Components\Hidden::make('received_by')->default(fn() => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();
                if ($user->hasAnyRole(['admin', 'super_admin'])) {
                    return $query;
                }
                if ($user->hasRole('woreda_coordinator')) {
                    // Woreda Coordinator sees pending_verification, verified, resolved, dismissed in their woreda
                    return $query->where('woreda_id', $user->woreda_id)
                                 ->whereIn('status', ['pending_verification', 'verified', 'resolved', 'dismissed']);
                }
                if ($user->hasRole('officer')) {
                    return $query->forOfficer();
                }
                if ($user->hasRole('paramilitary')) {
                    return $query->where('received_by', $user->id);
                }
                return $query->whereRaw('1=0'); // Default deny
            })
            ->columns([
                Tables\Columns\TextColumn::make('tip_code')->searchable(),
                Tables\Columns\TextColumn::make('suspect_name')->searchable(),
                Tables\Columns\TextColumn::make('violation_type')->badge(),
                Tables\Columns\TextColumn::make('woreda.name_am'),

                Tables\Columns\TextColumn::make('reported_date')->date(),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('submit')
                    ->label('Submit Tip / አቅርብ')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn($record) => $record->status === 'draft' && auth()->id() === $record->received_by)
                    ->requiresConfirmation()
                    ->action(function($record) {
                        $record->update(['status' => 'pending_verification']);
                        Notification::make()->title('Tip submitted for verification.')->success()->send();
                    }),

                Action::make('verify')
                    ->label('Verify / አረጋግጥ')
                    ->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn($record) => $record->status === 'pending_verification' && auth()->user()->can('verify_tips'))
                    ->requiresConfirmation()
                    ->action(function($record) {
                        $record->update(['status' => 'verified', 'verified_by' => auth()->id(), 'verified_at' => now()]);
                        Notification::make()->title('Tip verified.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject / ወደ ረቂቅ መልስ')
                    ->icon('heroicon-o-arrow-path')->color('warning')
                    ->visible(fn($record) => $record->status === 'pending_verification' && auth()->user()->can('verify_tips'))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_note')->label('Reason / ምክንያት')->required(),
                    ])
                    ->action(function($record, array $data) {
                        $record->update([
                            'status' => 'draft', 
                            'action_notes' => $data['rejection_note']
                        ]);
                        Notification::make()->title('Tip sent back to draft.')->warning()->send();
                    }),

                Action::make('dismiss')
                    ->label('Dismiss / ውድቅ አድርግ')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn($record) => $record->status === 'pending_verification' && auth()->user()->can('verify_tips'))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_note')->label('Reason / ምክንያት')->required(),
                    ])
                    ->action(function($record, array $data) {
                        $record->update([
                            'status' => 'dismissed', 
                            'action_notes' => $data['rejection_note'], 
                            'verified_by' => auth()->id(), 
                            'verified_at' => now()
                        ]);
                        Notification::make()->title('Tip marked as False Report & Dismissed.')->danger()->send();
                    }),

                Action::make('take_action')
                    ->label('Resolve / እርምጃ ይዝገቡ')
                    ->icon('heroicon-o-shield-check')->color('danger')
                    ->visible(fn($record) => in_array($record->status, ['verified']) && auth()->user()->can('take_action_on_tips'))
                    ->form([
                        Forms\Components\Select::make('action_taken')
                            ->options([
                                'formal_warning'    => 'Formal Warning',
                                'financial_penalty' => 'Financial Penalty / ቅጣት',
                                'asset_confiscation'=> 'Asset Confiscation / ንብረት መውረስ',
                                'legal_referral'    => 'Legal Referral',
                                'no_action'         => 'No Action',
                            ])->required(),
                        Forms\Components\Textarea::make('action_notes')->label('Notes (ማስታወሻ)'),
                        Forms\Components\DatePicker::make('action_date')->label('Action Date')->default(now()),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'action_taken' => $data['action_taken'],
                            'action_notes' => $data['action_notes'],
                            'action_date'  => $data['action_date'],
                            'status'       => 'resolved',
                            'investigated_by' => auth()->id(),
                        ]);
                        Notification::make()->title('Action logged successfully.')->success()->send();
                    }),
                    
                EditAction::make()
                    ->visible(fn($record) => $record->status === 'draft' || auth()->user()->hasAnyRole(['admin', 'super_admin'])),
            ])
            ->bulkActions([
                // Intentionally left empty to prevent bulk deletion.
                // SoftDeletes are enforced and no generic delete is accessible.
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVolunteerTips::route('/'),
            'create' => Pages\CreateVolunteerTip::route('/create'),
            'edit' => Pages\EditVolunteerTip::route('/{record}/edit'),
            'view' => Pages\ViewVolunteerTip::route('/{record}'),
        ];
    }
}
