<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AwarenessEngagementResource\Pages;
use App\Models\AwarenessEngagement;
use App\Models\SubCity;
use App\Models\Woreda;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ViewAction as TableViewAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;




class AwarenessEngagementResource extends Resource
{
    protected static ?string $model = AwarenessEngagement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|\UnitEnum|null $navigationGroup  = 'Awareness Management';
    protected static ?string $navigationLabel = 'Engagement Logs | ምዝገባ';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('paramilitary');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('የግንዛቤ ማስጨበጫ መዝገብ - Awareness Engagement Record')
                    ->description('Fill in all required fields accurately for the engagement report.')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        // ── Sub-Section: Objective ──
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('campaign_id')
                                    ->label(__('Select Active Campaign / ዘመቻ ይምረጡ'))
                                    ->options(function () {
                                        $query = \App\Models\Campaign::active();
                                        $user = auth()->user();
                                        if (($user->hasRole('paramilitary') || $user->hasRole('woreda_coordinator')) && $user->woreda_id) {
                                            $query->where('woreda_id', $user->woreda_id);
                                        }
                                        return $query->pluck('name_am', 'id')->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $campaign = \App\Models\Campaign::find($state);
                                            if ($campaign) {
                                                if ($campaign->category) $set('engagement_type', $campaign->category);
                                                $user = auth()->user();
                                                if (!($user->hasRole('paramilitary') && $user->woreda_id)) {
                                                    if ($campaign->sub_city_id) $set('sub_city_id', $campaign->sub_city_id);
                                                    if ($campaign->woreda_id) $set('woreda_id', $campaign->woreda_id);
                                                }
                                            }
                                        }
                                    }),

                                Forms\Components\Select::make('engagement_type')
                                    ->label('Engagement Strategy / የግንዛቤ ዓይነት')
                                    ->options([
                                        'house_to_house'  => 'ቤት ለቤት (House to House)',
                                        'coffee_ceremony' => 'ቡና ጠጡ (Coffee Ceremony)',
                                        'organization'    => 'በአደረጃጀት (Organization)',
                                    ])
                                    ->required()
                                    ->live(),
                            ]),


                        // ── Sub-Section: Dynamic Profiles ──
                        Group::make([
                            Forms\Components\TextInput::make('citizen_name')
                                ->label('Citizen Name / የዜጋው ሙሉ ስም')
                                ->placeholder('Full name as stated')
                                ->required(),
                            Grid::make(3)
                                ->schema([
                                    Forms\Components\Select::make('citizen_gender')
                                        ->label('Gender')
                                        ->options(['male' => 'Male / ወንድ', 'female' => 'Female / ሴት'])
                                        ->required(),
                                    Forms\Components\TextInput::make('citizen_age')
                                        ->label('Age')
                                        ->numeric()
                                        ->suffix('years old'),
                                ]),
                        ])->visible(fn (Get $get) => $get('engagement_type') === 'house_to_house'),

                        Group::make([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('headcount')
                                        ->label('Attendance Count / የታዳሚ ብዛት')
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\TextInput::make('stakeholder_partner')
                                        ->label('Partner / አጋር አካል'),
                                ]),
                        ])->visible(fn (Get $get) => $get('engagement_type') === 'coffee_ceremony'),

                        Group::make([
                            Forms\Components\Select::make('organization_type')
                                ->label('Organization Detail / የአደረጃጀት ዝርዝር')
                                ->options([
                                    'womens_association'    => 'ሴት ማህበር — Women\'s Association',
                                    'youth_association'     => 'ወጣት ማህበር — Youth Association',
                                    'edir'                  => 'እድር — Edir',
                                    'religious_institution' => 'የሀይማኖት ተቋማት — Religious Institution',
                                    'block_leaders'         => 'ብሎክ አመራሮች — Block Leaders',
                                    'peace_army'            => 'የሰላም ሰራዊት — Peace Army',
                                    'equb'                  => 'እቁብ — Equb',
                                ])
                                ->required()
                                ->searchable(),
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('org_headcount_male')->label('Male Total / ወንድ ብዛት')->numeric(),
                                    Forms\Components\TextInput::make('org_headcount_female')->label('Female Total / ሴት ብዛት')->numeric(),
                                ]),
                        ])->visible(fn (Get $get) => $get('engagement_type') === 'organization'),

                        // ── Sub-Section: Participants ──
                        Forms\Components\Repeater::make('attendees')
                            ->label('Additional Participants / ተጨማሪ ተሳታፊዎች')
                            ->relationship('attendees')
                            ->schema([
                                Forms\Components\TextInput::make('name_am')->label('Name')->required(),
                                Forms\Components\Select::make('gender')->label('Gender')
                                    ->options(['male' => 'Male', 'female' => 'Female'])->required(),
                                Forms\Components\TextInput::make('age')->label('Age')->numeric()->required(),
                            ])->columns(3)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['name_am'] ?? null)
                            ->visible(fn (Get $get) => in_array($get('engagement_type'), ['house_to_house', 'coffee_ceremony'])),


                        // ── Sub-Section: Context & Verification ──
                        Grid::make(3)
                            ->schema([
                                Forms\Components\DateTimePicker::make('session_datetime')
                                    ->label('Date & Time')
                                    ->required()->default(now()),
                                Forms\Components\TextInput::make('round_number')
                                    ->label('Round / ዙር')
                                    ->numeric()->default(1)->required(),
                                Forms\Components\Select::make('violation_type')
                                    ->label('Violation Type')
                                    ->options(AwarenessEngagement::violationLabels())
                                    ->required(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('sub_city_id')
                                    ->label('Sub-City')
                                    ->options(\App\Models\SubCity::pluck('name_am', 'id'))
                                    ->required(),
                                Forms\Components\Select::make('woreda_id')
                                    ->label('Woreda')
                                    ->options(\App\Models\Woreda::pluck('name_am', 'id'))
                                    ->required(),
                                Forms\Components\TextInput::make('block_number')->label('Block No.'),
                            ]),


                        Grid::make(2)
                            ->schema([
                                Forms\Components\ViewField::make('officer_signature')
                                    ->view('filament.forms.components.offline-signature')
                                    ->required(),
                                Forms\Components\ViewField::make('violation_photo_path')
                                    ->view('filament.forms.components.offline-photo'),
                            ]),
                    ]),

                Forms\Components\Hidden::make('created_by')->default(fn() => auth()->id()),
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
                    // Coordinators see submitted/approved/rejected records in their Woreda
                    return $query->where('woreda_id', $user->woreda_id)
                                 ->whereIn('status', ['submitted', 'approved', 'rejected']);
                }
                if ($user->hasRole('paramilitary')) {
                    // Paramilitary only see their own logs
                    return $query->where('created_by', $user->id);
                }
                return $query->whereRaw('1=0'); // Default deny
            })
            ->columns([
                Tables\Columns\TextColumn::make('engagement_code')
                    ->label('Code')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('engagement_type')
                    ->badge()->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('campaign.name_am')
                    ->label('Campaign'),
                Tables\Columns\TextColumn::make('woreda.name_am'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('session_datetime')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // 1. Submit for Approval (Draft/Rejected -> Submitted)
                Action::make('submit')

                    ->label('Submit / አቅርብ')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn($record) => in_array($record->status, ['draft', 'rejected']) && auth()->id() === $record->created_by)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'submitted',
                            'rejection_note' => null,
                        ]);
                        Notification::make()->title('Logged and submitted for approval.')->success()->send();
                    }),

                // 2. Approve (Submitted -> Approved)
                Action::make('approve')

                    ->label('Approve / አጽድቅ')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'submitted' && auth()->user()->can('approve_engagements'))
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()->title('Engagement record approved.')->success()->send();
                    }),

                // 3. Reject (Submitted -> Rejected)
                Action::make('reject')

                    ->label('Reject / ውድቅ አድርግ')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'submitted' && auth()->user()->can('reject_engagements'))
                    ->form([
                        Forms\Components\Textarea::make('rejection_note')
                            ->label('Rejection Reason (ምክንያት)')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_note' => $data['rejection_note'],
                        ]);
                        Notification::make()->title('Record rejected and sent back.')->danger()->send();
                    }),

                TableViewAction::make(),
                EditAction::make()
                    ->visible(fn($record) => !in_array($record->status, ['submitted', 'approved']) || auth()->user()->hasAnyRole(['admin', 'super_admin'])),
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
            'index' => Pages\ListAwarenessEngagements::route('/'),
            'create' => Pages\CreateAwarenessEngagement::route('/create'),
            'view' => Pages\ViewAwarenessEngagement::route('/{record}'),
            'edit' => Pages\EditAwarenessEngagement::route('/{record}/edit'),
        ];
    }
}
