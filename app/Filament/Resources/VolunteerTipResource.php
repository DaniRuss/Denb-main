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



class VolunteerTipResource extends Resource
{
    protected static ?string $model = VolunteerTip::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';
    protected static string|\UnitEnum|null $navigationGroup  = 'Awareness Management';
    protected static ?string $navigationLabel = 'Volunteer Tips | ጥቆማ';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return auth()->user()->hasAnyRole(['paramilitary', 'admin', 'super_admin']) && auth()->user()->can('submit_tips');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('engagement_id')
                    ->relationship('engagement', 'engagement_code', fn ($query) => $query->forUser(auth()->user()))
                    ->label('Linked Engagement (optional)')
                    ->searchable(),
                Forms\Components\TextInput::make('suspect_name')->label('Suspect Name (ስም)'),
                Forms\Components\Select::make('violation_type')
                    ->options(AwarenessEngagement::violationLabels())->required(),
                Forms\Components\TextInput::make('violation_location')->label('Location (ቦታ / ቀጣና)')->required(),
                Forms\Components\Select::make('sub_city_id')
                    ->relationship('subCity', 'name_am')
                    ->required()
                    ->default(fn() => auth()->user()->sub_city_id)
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
                    ->default(fn() => auth()->user()->woreda_id)
                    ->required(),

                Forms\Components\TextInput::make('block_number')->label('Block No.'),
                Forms\Components\DatePicker::make('violation_date')->label('Date of Violation (ቀን)')->required(),
                Forms\Components\DatePicker::make('reported_date')->label('Date Reported')->default(now())->required(),
                Forms\Components\TextInput::make('volunteer_name')->label('Volunteer\'s Name (ጥቆማ ያቀረበው)'),
                Forms\Components\Toggle::make('is_anonymous')->label('Anonymous Tip?'),
                
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
                    return $query->where('woreda_id', $user->woreda_id);
                }
                if ($user->hasRole('officer')) {
                    return $query->forOfficer()->where('woreda_id', $user->woreda_id);
                }
                if ($user->hasRole('paramilitary')) {
                    return $query->where('received_by', $user->id);
                }
                return $query->whereRaw('1=0');
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
                Action::make('verify')
                    ->label('Verify / ያረጋግጡ')
                    ->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn($record) => $record->status === 'pending' && auth()->user()->can('verify_tips'))
                    ->action(fn($record) => $record->update(['status' => 'verified', 'verified_by' => auth()->id(), 'verified_at' => now()])),
                
                Action::make('dismiss')
                    ->label('Dismiss / ውድቅ አድርግ (ሐሰተኛ)')
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn($record) => $record->status === 'pending' && auth()->user()->can('verify_tips'))
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update(['status' => 'false_report', 'verified_by' => auth()->id(), 'verified_at' => now()])),

                Action::make('take_action')
                    ->label('Log Action / እርምጃ ይዝገቡ')
                    ->icon('heroicon-o-shield-check')->color('danger')
                    ->visible(fn($record) => in_array($record->status, ['verified','investigating']) && auth()->user()->hasRole(['officer','admin']))
                    ->form([
                        Forms\Components\Select::make('action_taken')
                            ->options([
                                'formal_warning'    => 'Formal Warning',
                                'financial_penalty' => 'Financial Penalty / ቅጣት',
                                'asset_confiscation'=> 'Asset Confiscation / ዕቃ ሙሌቀት',
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
                            'status'       => 'action_taken',
                            'investigated_by' => auth()->id(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()->title('Action logged')->success()->send();
                        
                        if ($data['action_taken'] === 'asset_confiscation') {
                            return redirect(\App\Filament\Resources\ConfiscatedAssets\ConfiscatedAssetResource::getUrl('create', ['tip_id' => $record->id]));
                        }
                    }),
                    
                EditAction::make()
                    ->visible(fn($record) => $record->status === 'pending' || auth()->user()->hasAnyRole(['admin', 'super_admin'])),
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
            'index' => Pages\ListVolunteerTips::route('/'),
            'create' => Pages\CreateVolunteerTip::route('/create'),
            'edit' => Pages\EditVolunteerTip::route('/{record}/edit'),
            'view' => Pages\ViewVolunteerTip::route('/{record}'),
        ];
    }
}
