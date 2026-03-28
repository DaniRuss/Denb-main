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
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Set;

class VolunteerTipResource extends Resource
{
    protected static ?string $model = VolunteerTip::class;

    public static function canCreate(): bool
    {
        return auth()->user() && auth()->user()->can('submit_tips');
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('Volunteer Tips');
    }

    public static function getModelLabel(): string
    {
        return __('Volunteer Tip');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Volunteer Tips');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Awareness Management');
    }
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Volunteer Tip Submission'))
                    ->description(__('Provide details regarding the suspected violation and person involved.'))
                    ->icon('heroicon-m-light-bulb')
                    ->schema([
                        // ── Sub-Section: Reference & Linking ──
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('engagement_id')
                                    ->relationship('engagement', 'engagement_code')
                                    ->label(__('Linked Engagement'))
                                    ->searchable()
                                    ->placeholder(__('Optional: Search by code'))
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $engagement = \App\Models\AwarenessEngagement::find($state);
                                            if ($engagement) {
                                                $set('sub_city_id', $engagement->sub_city_id);
                                                $set('woreda_id', $engagement->woreda_id);
                                                $set('block_number', $engagement->block_number);
                                                if ($engagement->violation_type) $set('violation_type', $engagement->violation_type);
                                            }
                                        }
                                    }),
                                Forms\Components\Select::make('violation_type')
                                    ->label(__('Violation Type'))
                                    ->options(AwarenessEngagement::violationLabels())
                                    ->required()
                                    ->prefixIcon('heroicon-m-exclamation-triangle'),
                            ]),


                        // ── Sub-Section: Suspect & Date ──
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('suspect_name')
                                    ->label(__('Suspect Name'))
                                    ->placeholder(__('Individual or Business name')),
                                Forms\Components\DatePicker::make('violation_date')
                                    ->label(__('Date of Act'))
                                    ->required(),
                            ]),


                        // ── Sub-Section: Source Identity ──
                        Grid::make(1)
                            ->schema([
                                Forms\Components\Toggle::make('is_anonymous')
                                    ->label(__('Anonymous Tip?'))
                                    ->live()
                                    ->onColor('danger')
                                    ->offColor('gray'),
                                
                                Forms\Components\TextInput::make('volunteer_name')
                                    ->label(__('Volunteer Full Name'))
                                    ->hidden(fn (callable $get) => $get('is_anonymous'))
                                    ->placeholder(__('Enter name for verification'))
                                    ->prefixIcon('heroicon-m-identification'),
                            ]),


                        // ── Sub-Section: Location ──
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('sub_city_id')
                                    ->label(__('Sub-City'))
                                    ->options(\App\Models\SubCity::pluck('name_am', 'id'))
                                    ->required()
                                    ->live(),
                                Forms\Components\Select::make('woreda_id')
                                    ->label(__('Woreda'))
                                    ->options(function (callable $get) {
                                        $subCityId = $get('sub_city_id');
                                        return $subCityId ? \App\Models\Woreda::where('sub_city_id', $subCityId)->pluck('name_am', 'id') : [];
                                    })
                                    ->required()
                                    ->live(),
                                Forms\Components\TextInput::make('block_number')->label(__('Block No.')),
                            ]),
                        
                        Forms\Components\Textarea::make('violation_location')
                            ->label(__('Specific Location Description'))
                            ->placeholder(__('Describe the exact spot (e.g., behind the market, near the bridge)'))
                            ->rows(2)
                            ->required(),


                        // ── Sub-Section: Verification & Timeline ──
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('reported_date')
                                    ->label(__('Receipt Date'))
                                    ->default(now())
                                    ->required(),
                                Forms\Components\TextInput::make('tip_code')
                                    ->label(__('Reference Code'))
                                    ->disabled()
                                    ->placeholder(__('Auto-generated')),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\ViewField::make('volunteer_signature_path')
                                    ->view('filament.forms.components.offline-signature')
                                    ->label(__('Signature'))
                                    ->required(),
                                
                                Forms\Components\ViewField::make('evidence_photo')
                                    ->label(__('Evidence Photo'))
                                    ->view('filament.forms.components.offline-photo'),
                            ]),
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
                Tables\Columns\TextColumn::make('tip_code')->label(__('Code'))->searchable(),
                Tables\Columns\TextColumn::make('suspect_name')->label(__('Suspect Name'))->searchable(),
                Tables\Columns\TextColumn::make('violation_type')->label(__('Violation Type'))->badge(),
                Tables\Columns\TextColumn::make('woreda.name_am')->label(__('Woreda')),

                Tables\Columns\TextColumn::make('reported_date')->label(__('Receipt Date'))->date(),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('submit')
                    ->label(__('Submit Tip'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn($record) => $record->status === 'draft' && auth()->id() === $record->received_by)
                    ->requiresConfirmation()
                    ->action(function($record) {
                        $record->update(['status' => 'pending_verification']);
                        Notification::make()->title(__('Tip submitted for verification.'))->success()->send();
                    }),

                Action::make('verify')
                    ->label(__('Verify'))
                    ->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn($record) => $record->status === 'pending_verification' && auth()->user()->can('verify_tips'))
                    ->requiresConfirmation()
                    ->action(function($record) {
                        $record->update(['status' => 'verified', 'verified_by' => auth()->id(), 'verified_at' => now()]);
                        Notification::make()->title(__('Tip verified.'))->success()->send();
                    }),

                Action::make('reject')
                    ->label(__('Reject to Draft'))
                    ->icon('heroicon-o-arrow-path')->color('warning')
                    ->visible(fn($record) => $record->status === 'pending_verification' && auth()->user()->can('verify_tips'))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_note')->label(__('Rejection Reason'))->required(),
                    ])
                    ->action(function($record, array $data) {
                        $record->update([
                            'status' => 'draft', 
                            'rejection_note' => $data['rejection_note']
                        ]);
                        Notification::make()->title(__('Tip sent back to draft.'))->warning()->send();
                    }),

                Action::make('dismiss')
                    ->label(__('Dismiss'))
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn($record) => $record->status === 'pending_verification' && auth()->user()->can('verify_tips'))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('rejection_note')->label(__('Rejection Reason'))->required(),
                    ])
                    ->action(function($record, array $data) {
                        $record->update([
                            'status' => 'dismissed', 
                            'rejection_note' => $data['rejection_note'], 
                            'verified_by' => auth()->id(), 
                            'verified_at' => now()
                        ]);
                        Notification::make()->title(__('Tip marked as False Report & Dismissed.'))->danger()->send();
                    }),

                Action::make('take_action')
                    ->label(__('Resolve'))
                    ->icon('heroicon-o-shield-check')->color('danger')
                    ->visible(fn($record) => in_array($record->status, ['verified']) && auth()->user()->can('take_action_on_tips'))
                    ->form([
                        Forms\Components\Select::make('action_taken')
                            ->label(__('Action Taken'))
                            ->options([
                                'formal_warning'    => __('Formal Warning'),
                                'financial_penalty' => __('Financial Penalty'),
                                'asset_confiscation'=> __('Asset Confiscation'),
                                'legal_referral'    => __('Legal Referral'),
                                'no_action'         => __('No Action'),
                            ])->required(),
                        Forms\Components\Textarea::make('action_notes')->label(__('Action Notes')),
                        Forms\Components\DatePicker::make('action_date')->label(__('Action Date'))->default(now()),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'action_taken' => $data['action_taken'],
                            'action_notes' => $data['action_notes'],
                            'action_date'  => $data['action_date'],
                            'status'       => 'resolved',
                            'investigated_by' => auth()->id(),
                        ]);
                        
                        if ($data['action_taken'] === 'asset_confiscation') {
                            Notification::make()->title(__('Action logged. Redirecting to Asset Mgt...'))->success()->send();
                            return redirect(\App\Filament\Resources\ConfiscatedAssetResource::getUrl('create', ['volunteer_tip_id' => $record->id]));
                        }
                        
                        Notification::make()->title(__('Action logged successfully.'))->success()->send();
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
