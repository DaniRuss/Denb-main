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
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Engagement Logs');
    }

    public static function getModelLabel(): string
    {
        return __('Engagement Log');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Engagement Logs');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Awareness Management');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('paramilitary');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Awareness Engagement Record'))
                    ->description(__('Fill in all required fields accurately for the engagement report.'))
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        // ── Sub-Section: Objective ──
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('campaign_id')
                                    ->label(__('Select Active Campaign'))
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
                                    ->label(__('Engagement Strategy'))
                                    ->options([
                                        'house_to_house'  => __('House to House'),
                                        'coffee_ceremony' => __('Coffee Ceremony'),
                                        'organization'    => __('Organization'),
                                    ])
                                    ->required()
                                    ->live(),
                            ]),


                        // ── Sub-Section: Dynamic Profiles ──
                        Group::make([
                            Forms\Components\TextInput::make('citizen_name')
                                ->label(__('Citizen Name'))
                                ->placeholder(__('Full name as stated'))
                                ->required(),
                            Grid::make(3)
                                ->schema([
                                    Forms\Components\Select::make('citizen_gender')
                                        ->label(__('Gender'))
                                        ->options(['male' => __('Male'), 'female' => __('Female')])
                                        ->required(),
                                    Forms\Components\TextInput::make('citizen_age')
                                        ->label(__('Age'))
                                        ->numeric()
                                        ->suffix(__('years old')),
                                ]),
                        ])->visible(fn (Get $get) => $get('engagement_type') === 'house_to_house'),

                        Group::make([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('headcount')
                                        ->label(__('Attendance Count'))
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\TextInput::make('stakeholder_partner')
                                        ->label(__('Partner Stakeholder')),
                                ]),
                        ])->visible(fn (Get $get) => $get('engagement_type') === 'coffee_ceremony'),

                        Group::make([
                            Forms\Components\Select::make('organization_type')
                                ->label(__('Organization Detail'))
                                ->options([
                                    'womens_association'    => __('Women\'s Association'),
                                    'youth_association'     => __('Youth Association'),
                                    'edir'                  => __('Edir'),
                                    'religious_institution' => __('Religious Institution'),
                                    'block_leaders'         => __('Block Leaders'),
                                    'peace_army'            => __('Peace Army'),
                                    'equb'                  => __('Equb'),
                                ])
                                ->required()
                                ->searchable(),
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('org_headcount_male')->label(__('Male Total'))->numeric(),
                                    Forms\Components\TextInput::make('org_headcount_female')->label(__('Female Total'))->numeric(),
                                ]),
                        ])->visible(fn (Get $get) => $get('engagement_type') === 'organization'),

                        // ── Sub-Section: Participants ──
                        Forms\Components\Repeater::make('attendees')
                            ->label(__('Additional Participants'))
                            ->relationship('attendees')
                            ->schema([
                                Forms\Components\TextInput::make('name_am')->label(__('Name'))->required(),
                                Forms\Components\Select::make('gender')->label(__('Gender'))
                                    ->options(['male' => __('Male'), 'female' => __('Female')])->required(),
                                Forms\Components\TextInput::make('age')->label(__('Age'))->numeric()->required(),
                            ])->columns(3)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['name_am'] ?? null)
                            ->visible(fn (Get $get) => in_array($get('engagement_type'), ['house_to_house', 'coffee_ceremony'])),


                        // ── Sub-Section: Context & Verification ──
                        Grid::make(3)
                            ->schema([
                                Forms\Components\DateTimePicker::make('session_datetime')
                                    ->label(__('Date & Time'))
                                    ->required()->default(now()),
                                Forms\Components\TextInput::make('round_number')
                                    ->label(__('Round'))
                                    ->numeric()->default(1)->required(),
                                Forms\Components\Select::make('violation_type')
                                    ->label(__('Violation Type'))
                                    ->options(AwarenessEngagement::violationLabels())
                                    ->required(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('sub_city_id')
                                    ->label(__('Sub-City'))
                                    ->options(\App\Models\SubCity::pluck('name_am', 'id'))
                                    ->required(),
                                Forms\Components\Select::make('woreda_id')
                                    ->label(__('Woreda'))
                                    ->options(\App\Models\Woreda::pluck('name_am', 'id'))
                                    ->required(),
                                Forms\Components\TextInput::make('block_number')->label(__('Block No.')),
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
                    ->label(__('Code'))->searchable()->copyable(),
                Tables\Columns\TextColumn::make('engagement_type')
                    ->label(__('Engagement Strategy'))
                    ->badge()->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('campaign.name_am')
                    ->label(__('Campaign')),
                Tables\Columns\TextColumn::make('woreda.name_am')
                    ->label(__('Woreda')),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('session_datetime')->label(__('Date & Time'))->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // 1. Submit for Approval (Draft/Rejected -> Submitted)
                Action::make('submit')

                    ->label(__('Submit'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn($record) => in_array($record->status, ['draft', 'rejected']) && auth()->id() === $record->created_by)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'submitted',
                            'rejection_note' => null,
                        ]);
                        Notification::make()->title(__('Logged and submitted for approval.'))->success()->send();
                    }),

                // 2. Approve (Submitted -> Approved)
                Action::make('approve')

                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'submitted' && auth()->user()->can('approve_engagements'))
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()->title(__('Engagement record approved.'))->success()->send();
                    }),

                // 3. Reject (Submitted -> Rejected)
                Action::make('reject')

                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'submitted' && auth()->user()->can('reject_engagements'))
                    ->form([
                        Forms\Components\Textarea::make('rejection_note')
                            ->label(__('Rejection Reason'))
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_note' => $data['rejection_note'],
                        ]);
                        Notification::make()->title(__('Record rejected and sent back.'))->danger()->send();
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
