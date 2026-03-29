<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\SubCity;
use App\Models\Woreda;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;


class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Campaigns');
    }

    public static function getModelLabel(): string
    {
        return __('Campaign');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Campaigns');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Awareness Management');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'super_admin', 'woreda_coordinator', 'officer']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'super_admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Campaign Record'))
                    ->description(__('Fill in all required fields accurately for the campaign.'))
                    ->icon('heroicon-m-megaphone')
                    ->schema([

                        Forms\Components\TextInput::make('name_am')
                            ->label(__('Campaign Name (Amharic)'))
                            ->required()
                            ->placeholder('ለምሳሌ፡ ህገ-ወጥ የሰዎች ዝውውር መከላከል')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name_en')
                            ->label(__('Campaign Name (English)'))
                            ->required()
                            ->placeholder('e.g. Combatting Human Trafficking')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description_am')
                            ->label(__('Description (Amharic)'))
                            ->rows(3),

                        Forms\Components\Textarea::make('description_en')
                            ->label(__('Description (English)'))
                            ->rows(3),

                        Forms\Components\Select::make('category')
                            ->label(__('Campaign Category'))
                            ->options([
                                'house_to_house'  => __('House to House'),
                                'coffee_ceremony' => __('Coffee Ceremony'),
                                'organization'    => __('Organization'),
                            ])
                            ->required()
                            ->live()
                            ->helperText(__('House to House: One-on-one citizen visits | Coffee Ceremony: Group sessions | Organization: Community associations')),

                        Forms\Components\Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'draft'     => __('Draft'),
                                'active'    => __('Active'),
                                'completed' => __('Completed'),
                                'cancelled' => __('Cancelled'),
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\DatePicker::make('start_date')
                            ->label(__('Start Date'))
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label(__('End Date'))
                            ->required()
                            ->after('start_date'),

                        Forms\Components\Select::make('sub_city_id')
                            ->label(__('Sub-City'))
                            ->options(SubCity::orderBy('name_am')->pluck('name_am', 'id')->toArray())
                            ->live()
                            ->searchable()
                            ->afterStateUpdated(fn (callable $set) => $set('woreda_id', null))
                            ->required(),

                        Forms\Components\Select::make('woreda_id')
                            ->label(__('Woreda'))
                            ->options(function (callable $get) {
                                $subCityId = $get('sub_city_id');
                                if (!$subCityId) return [];
                                return Woreda::where('sub_city_id', $subCityId)
                                    ->orderBy('name_am')->pluck('name_am', 'id')->toArray();
                            })
                            ->live()
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('block')
                            ->label(__('Block'))
                            ->placeholder(__('e.g. Block 5')),

                        Forms\Components\Textarea::make('specific_place')
                            ->label(__('Specific Place Name'))
                            ->placeholder(__('e.g. Near the main market, behind the school'))
                            ->rows(2),

                        // ── Target Audience (Organization only) ──
                        Group::make([
                            Forms\Components\Textarea::make('target_audience')
                                ->label(__('Organizations / Associations'))
                                ->placeholder(__('List the community groups or associations to be engaged'))
                                ->rows(4),
                        ])->visible(fn (callable $get) => $get('category') === 'organization'),

                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('campaign_code')
                    ->label(__('Code'))
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name_am')
                    ->label(__('Campaign Name (Amharic)'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label(__('Campaign Name (English)'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('category')
                    ->label(__('Category'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'house_to_house'  => __('House to House'),
                        'coffee_ceremony' => __('Coffee Ceremony'),
                        'organization'    => __('Organization'),
                        default           => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'house_to_house'  => 'info',
                        'coffee_ceremony' => 'warning',
                        'organization'    => 'success',
                        default           => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')->label(__('Start Date'))->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->label(__('End Date'))->date()->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft'     => 'gray',
                        'active'    => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default     => 'secondary',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('Category'))
                    ->options([
                        'house_to_house'  => __('House to House'),
                        'coffee_ceremony' => __('Coffee Ceremony'),
                        'organization'    => __('Organization'),
                    ]),
                Tables\Filters\SelectFilter::make('status')->label(__('Status'))
                    ->options([
                        'draft'     => __('Draft'),
                        'active'    => __('Active'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                    ]),
            ])
            ->actions([
                EditAction::make(),
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
            'index'  => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit'   => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
