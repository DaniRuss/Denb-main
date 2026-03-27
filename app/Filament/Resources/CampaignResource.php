<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\SubCity;
use App\Models\Woreda;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
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
    protected static string|\UnitEnum|null $navigationGroup  = 'Awareness Management';
    protected static ?string $navigationLabel = 'Campaigns | ዘመቻዎች';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'super_admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // ── Section 1: Campaign Identity ──
                Section::make('Campaign Identity | ዘመቻ መለያ')
                    ->icon('heroicon-o-megaphone')
                    ->schema([
                        Forms\Components\TextInput::make('name_am')
                            ->label('Campaign Name (አማርኛ)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_en')
                            ->label('Campaign Name (English)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description_am')
                            ->label('Description (አማርኛ ዝርዝር)')
                            ->rows(3),
                        Forms\Components\Textarea::make('description_en')
                            ->label('Description (English)')
                            ->rows(3),
                    ])->columns(2),

                // ── Section 2: Campaign Category ──
                Section::make('Campaign Category | ዘመቻ ዓይነት')
                    ->icon('heroicon-o-squares-2x2')
                    ->description('Select the type of awareness campaign to be conducted / ዘመቻው እንዴት እንደሚካሄድ ይምረጡ')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->label('Campaign Category (ዘመቻ ዓይነት)')
                            ->options([
                                'house_to_house'  => 'ቤት ለቤት — House to House',
                                'coffee_ceremony' => 'ቡና ጠጡ — Coffee Ceremony',
                                'organization'    => 'በአደረጃጀት — Organizational / Community',
                            ])
                            ->required()
                            ->live()
                            ->helperText('House to House: One-on-one citizen visits | Coffee Ceremony: Group sessions | Organizational: Community associations'),
                    ]),

                // ── Section 3: Timeline ──
                Section::make('Campaign Timeline | የዘመቻ ጊዜ ሰሌዳ')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date (መጀመሪያ ቀን)')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date (ማጠናቀቂያ ቀን)')
                            ->required()
                            ->after('start_date'),
                    ])->columns(2),

                // ── Section 4: Target Location (all campaign types) ──
                Section::make('Target Location | ዒላማ አካባቢ')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Select::make('sub_city_id')
                            ->label('Sub-City (ክፍለ ከተማ)')
                            ->options(
                                SubCity::orderBy('name_am')->pluck('name_am', 'id')->toArray()
                            )
                            ->live()
                            ->searchable()
                            ->afterStateUpdated(fn (callable $set) => $set('woreda_id', null))
                            ->required(),
                        Forms\Components\Select::make('woreda_id')
                            ->label('Woreda (ወረዳ)')
                            ->options(function (callable $get) {
                                $subCityId = $get('sub_city_id');
                                if (!$subCityId) {
                                    return [];
                                }
                                return Woreda::where('sub_city_id', $subCityId)
                                    ->orderBy('name_am')
                                    ->pluck('name_am', 'id')
                                    ->toArray();
                            })
                            ->live()
                            ->searchable()
                            ->required(),

                        // ── Block + Specific Place: shown for all campaigns ──
                        Forms\Components\TextInput::make('block')
                            ->label('Block / ብሎክ')
                            ->placeholder('e.g. Block 5 / ብሎክ 5'),

                        Forms\Components\Textarea::make('specific_place')
                            ->label('Specific Place Name (ዝርዝር ቦታ)')
                            ->placeholder('e.g. Near the main market, behind the school / ዋናው ገበያ አጠገብ ትምህርት ቤቱ ጀርባ')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                // ── Section 5: Target Audience — only for Organizational campaigns ──
                Section::make('Target Audience | ዒላማ ህብረተሰብ')
                    ->icon('heroicon-o-users')
                    ->description('Specify the community groups or associations to be engaged / ተሳታፊ የሚሆኑ ቡድኖችና ማህበራትን ይዘርዝሩ')
                    ->visible(fn (callable $get) => $get('category') === 'organization')
                    ->schema([
                        Forms\Components\Textarea::make('target_audience')
                            ->label('Organizations / Associations (ድርጅቶች / ማህበራት)')
                            ->placeholder("List the community groups or organizations, e.g.:\n- Women's savings group — ሴቶች ቁጠባ ማህበር\n- Youth association — የወጣቶች ማህበር\n- Business owners association — የነጋዴዎች ማህበር")
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),


                // ── Section 6: Campaign Status ──
                Section::make('Campaign Status | ሁኔታ')
                    ->icon('heroicon-o-check-badge')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status (ሁኔታ)')
                            ->options([
                                'draft'     => 'Draft — ረቂቅ',
                                'active'    => 'Active — ንቁ',
                                'completed' => 'Completed — ተጠናቋል',
                                'cancelled' => 'Cancelled — ተሰርዟል',
                            ])
                            ->default('draft')
                            ->required(),
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
                    ->label('Code')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name_am')
                    ->label('Campaign (አማርኛ)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('Campaign (English)')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'house_to_house'  => 'ቤት ለቤት',
                        'coffee_ceremony' => 'ቡና ጠጡ',
                        'organization'    => 'በአደረጃጀት',
                        default           => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'house_to_house'  => 'info',
                        'coffee_ceremony' => 'warning',
                        'organization'    => 'success',
                        default           => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('status')
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
                    ->label('Category')
                    ->options([
                        'house_to_house'  => 'ቤት ለቤት (House to House)',
                        'coffee_ceremony' => 'ቡና ጠጡ (Coffee Ceremony)',
                        'organization'    => 'በአደረጃጀት (Organization)',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'active'    => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
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
