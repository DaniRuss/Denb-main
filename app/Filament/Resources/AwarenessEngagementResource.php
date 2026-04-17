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
use Filament\Actions\ViewAction as TableViewAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
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

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'super_admin', 'woreda_coordinator', 'paramilitary']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole('paramilitary');
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        // Only creator can edit, and only if NOT yet submitted/approved
        // AND the campaign end date has not passed
        if (auth()->id() !== $record->created_by || !in_array($record->status, ['draft', 'rejected'])) {
            return false;
        }

        // Check campaign end date visibility constraint
        if ($record->campaign && $record->campaign->end_date && today()->gt($record->campaign->end_date)) {
            return false;
        }

        return true;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        // Creator (Field Role) can delete drafts/rejected if campaign end date hasn't passed
        if (auth()->id() === $record->created_by && in_array($record->status, ['draft', 'rejected'])) {
             if ($record->campaign && $record->campaign->end_date && today()->gt($record->campaign->end_date)) {
                return false;
            }
            return true;
        }

        // Super Admin can always delete for cleanup
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make(__('Awareness Engagement Record'))
                    ->description(__('Fill in all required fields accurately for the engagement report.'))
                    ->icon('heroicon-m-document-text')
                    ->columns(1)
                    ->schema([
                        // ── Sub-Section: Objective ──
                        Grid::make(1)
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
                            Grid::make(1)
                                ->schema([
                                    Forms\Components\Select::make('citizen_gender')
                                        ->label(__('Gender'))
                                        ->options(['male' => __('Male'), 'female' => __('Female')])
                                        ->required(),
                                    Forms\Components\TextInput::make('citizen_age')
                                        ->label(__('Age'))
                                        ->numeric()
                                        ->suffix(__('years old')),
                                    Forms\Components\Hidden::make('citizen_registration_date')
                                        ->default(now()),
                                ]),
                        ])->visible(fn (Get $get) => $get('engagement_type') === 'house_to_house'),

                        Group::make([
                            Grid::make(1)
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
                            Grid::make(1)
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
                            ])->columns(1)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['name_am'] ?? null)
                            ->visible(fn (Get $get) => $get('engagement_type') === 'coffee_ceremony'),


                        // ── Sub-Section: Context & Verification ──
                        Grid::make(1)
                            ->schema([
                                Forms\Components\Hidden::make('session_datetime')
                                    ->default(now()),
                                Forms\Components\TextInput::make('round_number')
                                    ->label(__('Round'))
                                    ->numeric()->default(1)->required(),
                                Forms\Components\Select::make('violation_type')
                                    ->label(__('Violation Type (የጥሰት አይነት)'))
                                    ->options(AwarenessEngagement::violationLabels())
                                    ->required(),
                            ]),

                        Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('sub_city_id')
                                    ->label(__('Sub-City'))
                                    ->options(\App\Models\SubCity::orderBy('name_am')->pluck('name_am', 'id')->toArray())
                                    ->default(fn () => \App\Helpers\JurisdictionHelper::getSubCityId())
                                    ->visible(fn () => auth()->user()->hasRole('super_admin'))
                                    ->required(),

                                Forms\Components\TextInput::make('sub_city_display')
                                    ->label(__('Sub-City'))
                                    ->default(fn () => \App\Helpers\JurisdictionHelper::getSubCityName())
                                    ->readOnly()
                                    ->visible(fn () => !auth()->user()->hasRole('super_admin')),

                                Forms\Components\Hidden::make('sub_city_id')
                                    ->default(fn () => \App\Helpers\JurisdictionHelper::getSubCityId())
                                    ->visible(fn () => !auth()->user()->hasRole('super_admin')),

                                Forms\Components\Select::make('woreda_id')
                                    ->label(__('Woreda'))
                                    ->options(function () {
                                        $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId();
                                        if (!$subCityId) return [];
                                        return \App\Models\Woreda::where('sub_city_id', $subCityId)
                                            ->orderBy('name_am')
                                            ->pluck('name_am', 'id')
                                            ->toArray();
                                    })
                                    ->default(fn () => \App\Helpers\JurisdictionHelper::getWoredaId())
                                    ->visible(fn () => auth()->user()->hasRole('super_admin') || !\App\Helpers\JurisdictionHelper::getWoredaId())
                                    ->required(),

                                Forms\Components\TextInput::make('woreda_display')
                                    ->label(__('Woreda'))
                                    ->default(fn () => \App\Models\Woreda::find(\App\Helpers\JurisdictionHelper::getWoredaId())?->name_am ?? '—')
                                    ->readOnly()
                                    ->visible(fn () => !auth()->user()->hasRole('super_admin') && \App\Helpers\JurisdictionHelper::getWoredaId()),

                                Forms\Components\Hidden::make('woreda_id')
                                    ->default(fn () => \App\Helpers\JurisdictionHelper::getWoredaId())
                                    ->visible(fn () => !auth()->user()->hasRole('super_admin') && \App\Helpers\JurisdictionHelper::getWoredaId()),
                                Forms\Components\TextInput::make('block_number')
                                    ->label(__('Block No.')),
                            ]),


                        Grid::make(1)
                            ->schema([
                                Forms\Components\ViewField::make('officer_signature')
                                    ->view('filament.forms.components.offline-signature')
                                    ->required(),
                                Forms\Components\ViewField::make('violation_photo_path')
                                    ->view('filament.forms.components.offline-photo'),
                            ]),
                        
                        Forms\Components\Textarea::make('final_description')
                            ->label(__('Final Description'))
                            ->placeholder(__('Enter any additional details or final description about the engagement'))
                            ->nullable()
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('rejection_note')
                            ->label(__('Rejection Reason'))
                            ->visible(fn ($record) => $record && $record->status === 'rejected')
                            ->readOnly()
                            ->columnSpanFull()
                            ->helperText(__('This note was provided by the coordinator during rejection.')),
                    ]),

                Forms\Components\Hidden::make('created_by')->default(fn() => auth()->id()),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();
                if ($user->hasRole('super_admin')) {
                    return $query;
                }

                if ($user->hasRole('admin')) {
                    $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
                    return $query->where('sub_city_id', $subCityId);
                }
                
                // Field roles (Paramilitary / Field) see their own logs always
                if ($user->hasAnyRole(['paramilitary', 'field'])) {
                    return $query->where('created_by', $user->id);
                }

                if ($user->hasRole('woreda_coordinator')) {
                    $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
                    return $query->where('woreda_id', $woredaId)
                                 ->whereIn('status', ['submitted', 'approved', 'rejected']);
                }

                return $query->whereRaw('1=0'); // Default deny
            })
            ->columns([
                Tables\Columns\TextColumn::make('engagement_code')
                    ->label(__('Code'))->searchable()->copyable()->hidden(),
                Tables\Columns\TextColumn::make('engagement_type')
                    ->label(__('Engagement Strategy'))
                    ->badge()->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('campaign.name_am')
                    ->label(__('Campaign')),
                Tables\Columns\TextColumn::make('woreda.name_am')
                    ->label(__('Woreda')),
                Tables\Columns\TextColumn::make('subCity.name_am')
                    ->label(__('Sub-City')),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label(__('Created By')),
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
                    ->visible(fn($record) => 
                        in_array($record->status, ['draft', 'rejected']) && 
                        auth()->id() === $record->created_by &&
                        (!$record->campaign || !$record->campaign->end_date || today()->lte($record->campaign->end_date))
                    )
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
                    ->visible(fn($record) => $record->status === 'submitted' && auth()->user()->hasRole('woreda_coordinator'))
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
                    ->visible(fn($record) => $record->status === 'submitted' && auth()->user()->hasRole('woreda_coordinator'))
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
                    ->visible(fn($record) => static::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn($record) => static::canDelete($record)),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');

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
