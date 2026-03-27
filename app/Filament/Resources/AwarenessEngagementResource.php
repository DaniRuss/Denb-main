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
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;




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
                Forms\Components\Select::make('campaign_id')
                    ->label(__('Campaign (ዘመቻ)'))
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
                    ->placeholder('Select Campaign')
                    ->disablePlaceholderSelection()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if ($state) {
                            $campaign = \App\Models\Campaign::find($state);
                            if ($campaign) {
                                if ($campaign->category) {
                                    $set('engagement_type', $campaign->category);
                                }
                                $user = auth()->user();
                                $isLockedParamilitary = $user->hasRole('paramilitary') && $user->woreda_id !== null;
                                if (! $isLockedParamilitary) {
                                    if ($campaign->sub_city_id) $set('sub_city_id', $campaign->sub_city_id);
                                    if ($campaign->woreda_id) $set('woreda_id', $campaign->woreda_id);
                                }
                            }
                        }
                    }),

                Forms\Components\Select::make('engagement_type')
                    ->label('Engagement Type (የግንዛቤ ዓይነት)')
                    ->options([
                        'house_to_house'  => 'ቤት ለቤት (House to House)',
                        'coffee_ceremony' => 'ቡና ጠጡ (Coffee Ceremony)',
                        'organization'    => 'በአደረጃጀት (Organization)',
                    ])
                    ->required()
                    ->placeholder('Select Engagement Type (የግንዛቤ ዓይነት ይምረጡ)'),

                // ── Section 3: Geography ──
                Forms\Components\Select::make('sub_city_id')
                    ->label('Sub-City (ክፍለ ከተማ)')
                    ->options(SubCity::orderBy('name_am')->pluck('name_am', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->placeholder('Select Sub-City (ክፍለ ከተማ ይምረጡ)'),
                
                Forms\Components\Select::make('woreda_id')
                    ->label('Woreda (ወረዳ)')
                    // Load ALL woredas upfront so the form works offline, grouped by Sub-City is ideal but simple flat list is safest for offline caching.
                    // When offline, live() dependent queries fail. By pre-filling all or removing live() dependency, JS can still submit.
                    ->options(Woreda::orderBy('name_am')->pluck('name_am', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->placeholder('Select Woreda (ወረዳ ይምረጡ)'),

                Forms\Components\TextInput::make('block_number')->label('Block No. (ብሎክ ቁጥር)'),

                // ── Section 4: Violation Type ——
                Forms\Components\Select::make('violation_type')
                    ->label('Violation Type (የደንብ መተላለፍ ዓይነት)')
                    ->options(AwarenessEngagement::violationLabels())
                    ->required()
                    ->placeholder('Select Violation (የደንብ መተላለፍ ዓይነት ይምረጡ)'),

                Forms\Components\TextInput::make('round_number')
                    ->label('Round / ዙር (ለስንተኛ ግዜ)')
                    ->numeric()->default(1)->required(),

                // ── Section 5A: House-to-House Fields ──
                Section::make('ቤት ለቤት — Citizen Details')
                    ->schema([
                        Forms\Components\TextInput::make('citizen_name')
                            ->label('Primary Citizen Name (ዋና ተሳታፊ ስም)')
                            ->required(),
                        Forms\Components\Select::make('citizen_gender')->label('Gender (ጾታ)')
                            ->options(['male' => 'Male / ወንድ', 'female' => 'Female / ሴት'])
                            ->required()
                            ->placeholder('Select Gender (ጾታ ይምረጡ)'),
                        Forms\Components\TextInput::make('citizen_age')->label('Age (እድሜ)')->numeric(),
                    ])
                    ->extraAttributes(['x-show' => "data.engagement_type === 'house_to_house'"]),

                // ── Section 5B: Coffee Ceremony Fields ──
                Section::make('ቡና ጠጡ — Group Details')
                    ->schema([
                        Forms\Components\TextInput::make('headcount')->label('Total Headcount (ብዛት)')->numeric()->requiredWith('stakeholder_partner'),
                        Forms\Components\TextInput::make('stakeholder_partner')->label('Stakeholder Partner (ባለድርሻ አካል)'),
                    ])
                    ->extraAttributes(['x-show' => "data.engagement_type === 'coffee_ceremony'"]),

                // ── Section 5C: Organization Fields ──
                Section::make('በአደረጃጀት — Organization Details')
                    ->schema([
                        Forms\Components\Select::make('organization_type')
                            ->label('Organization (አደረጃጀት ስም)')
                            ->options([
                                'womens_association'    => 'ሴት ማህበር (Women\'s Association)',
                                'youth_association'     => 'ወጣት ማህበር (Youth Association)',
                                'edir'                  => 'እድር (Edir)',
                                'religious_institution' => 'የሀይማኖት ተቋማት (Religious Institution)',
                                'block_leaders'         => 'ብሎክ አመራሮች (Block Leaders)',
                                'peace_army'            => 'የሰላም ሰራዊት (Peace Army)',
                                'equb'                  => 'እቁብ (Equb)',
                            ])
                            ->placeholder(null)
                            ->disablePlaceholderSelection(),
                        Forms\Components\TextInput::make('org_headcount_male')->label('Male Headcount')->numeric(),
                        Forms\Components\TextInput::make('org_headcount_female')->label('Female Headcount')->numeric(),
                    ])
                    ->extraAttributes(['x-show' => "data.engagement_type === 'organization'"]),

                // ── Unified Attendees Repeater (H2H & Coffee) ──
                Forms\Components\Repeater::make('attendees')
                    ->relationship('attendees')
                    ->schema([
                        Forms\Components\TextInput::make('name_am')
                            ->label('Name / ስም')
                            ->required(),
                        Forms\Components\Select::make('gender')->label('Gender / ጾታ')
                            ->options(['male' => 'Male / ወንድ', 'female' => 'Female / ሴት'])
                            ->required()
                            ->placeholder('Select Gender (ጾታ ይምረጡ)'),
                        Forms\Components\TextInput::make('age')->label('Age / እድሜ')->numeric(),
                    ])
                    ->collapsible()
                    ->label(fn ($get) => $get('engagement_type') === 'house_to_house' 
                        ? 'Additional Persons / ተጨማሪ ሰዎች' 
                        : 'Individual Attendees (optional)'
                    )
                    ->minItems(0)
                    ->defaultItems(0)
                    ->addActionLabel('Add Attendee / ተጨማሪ ጨምር')
                    ->extraAttributes(['x-show' => "['house_to_house', 'coffee_ceremony'].includes(data.engagement_type)"]),

                // ── Section 6: Timestamp ──
                Forms\Components\DateTimePicker::make('session_datetime')
                    ->label('Session Date/Time (ሰዓት፣ ቀን)')->required(),
                
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
            'edit' => Pages\EditAwarenessEngagement::route('/{record}/edit'),
        ];
    }
}
