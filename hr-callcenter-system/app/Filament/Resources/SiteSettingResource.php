<?php
// app/Filament/Resources/SiteSettingResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use App\Models\User;
use Filament\Actions\EditAction;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $pluralLabel = 'Site Settings';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Site Settings')
                    ->tabs([
                        // TAB 1: GENERAL SETTINGS
                        Tab::make('General')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Section::make('Organization Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('organization_name_am')
                                            ->label('Organization Name (አማርኛ)')
                                            ->required()
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('organization_name_am')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('organization_name_am', $state)),

                                        Forms\Components\TextInput::make('organization_name_en')
                                            ->label('Organization Name (English)')
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('organization_name_en')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('organization_name_en', $state)),

                                        Forms\Components\TextInput::make('tagline_am')
                                            ->label('Tagline (አማርኛ)')
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('tagline_am')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('tagline_am', $state)),

                                        Forms\Components\TextInput::make('tagline_en')
                                            ->label('Tagline (English)')
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('tagline_en')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('tagline_en', $state)),

                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('footer_text_am')
                                                    ->label('Footer Text (አማርኛ)')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('footer_text_am')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('footer_text_am', $state)),

                                                Forms\Components\TextInput::make('footer_text_en')
                                                    ->label('Footer Text (English)')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('footer_text_en')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('footer_text_en', $state)),
                                            ]),

                                        Forms\Components\TextInput::make('copyright_text')
                                            ->label('Copyright Text')
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('copyright_text')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('copyright_text', $state)),
                                    ])->columns(2),

                                Section::make('Logos & Branding')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                FileUpload::make('logo_light')
                                                    ->label('Logo (Light Background)')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('site/logo')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('logo_light')))
                                                    ->afterStateUpdated(fn($state) => $state && SiteSetting::set('logo_light', $state)),

                                                FileUpload::make('logo_dark')
                                                    ->label('Logo (Dark Background)')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('site/logo')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('logo_dark')))
                                                    ->afterStateUpdated(fn($state) => $state && SiteSetting::set('logo_dark', $state)),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                FileUpload::make('favicon')
                                                    ->label('Favicon')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('site/favicon')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('favicon')))
                                                    ->afterStateUpdated(fn($state) => $state && SiteSetting::set('favicon', $state)),

                                                FileUpload::make('og_image')
                                                    ->label('Open Graph Image')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('site/og')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('og_image')))
                                                    ->afterStateUpdated(fn($state) => $state && SiteSetting::set('og_image', $state)),
                                            ]),
                                    ]),
                            ]),

                        // TAB 2: CONTACT INFORMATION
                        Tab::make('Contact')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Section::make('Contact Details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('phone_primary')
                                                    ->label('Primary Phone')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('phone_primary')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('phone_primary', $state)),

                                                Forms\Components\TextInput::make('phone_secondary')
                                                    ->label('Secondary Phone')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('phone_secondary')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('phone_secondary', $state)),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('email_primary')
                                                    ->label('Primary Email')
                                                    ->email()
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('email_primary')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('email_primary', $state)),

                                                Forms\Components\TextInput::make('email_secondary')
                                                    ->label('Secondary Email')
                                                    ->email()
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('email_secondary')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('email_secondary', $state)),
                                            ]),

                                        Forms\Components\Textarea::make('address_am')
                                            ->label('Address (አማርኛ)')
                                            ->rows(3)
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('address_am')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('address_am', $state)),

                                        Forms\Components\Textarea::make('address_en')
                                            ->label('Address (English)')
                                            ->rows(3)
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('address_en')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('address_en', $state)),
                                    ]),

                                Section::make('Social Media & Hours')
                                    ->schema([
                                        Repeater::make('working_hours')
                                            ->schema([
                                                Forms\Components\TextInput::make('days_am')->required(),
                                                Forms\Components\TextInput::make('days_en')->required(),
                                                Forms\Components\TextInput::make('hours')->required(),
                                            ])
                                            ->afterStateHydrated(fn($component, $state) => $component->state(json_decode(SiteSetting::get('working_hours', '[]'), true)))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('working_hours', json_encode($state))),
                                    ]),
                            ]),

                        // TAB 3: APPEARANCE
                        Tab::make('Appearance')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Section::make('Colors')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                ColorPicker::make('primary_color')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('primary_color')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('primary_color', $state)),
                                                ColorPicker::make('secondary_color')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('secondary_color')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('secondary_color', $state)),
                                                ColorPicker::make('accent_color')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('accent_color')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('accent_color', $state)),
                                            ]),
                                    ]),
                            ]),

                        // TAB 4: HERO SECTION
                        Tab::make('Hero')
                            ->icon('heroicon-o-megaphone')
                            ->schema([
                                Section::make('Hero Content')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('hero_title_am')
                                                    ->label('Hero Title (አማርኛ)')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('hero_title_am')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('hero_title_am', $state)),
                                                Forms\Components\TextInput::make('hero_title_en')
                                                    ->label('Hero Title (English)')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('hero_title_en')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('hero_title_en', $state)),

                                                Forms\Components\TextInput::make('hero_subtitle_am')
                                                    ->label('Hero Subtitle (አማርኛ)')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('hero_subtitle_am')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('hero_subtitle_am', $state)),
                                                Forms\Components\TextInput::make('hero_subtitle_en')
                                                    ->label('Hero Subtitle (English)')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('hero_subtitle_en')))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('hero_subtitle_en', $state)),
                                            ]),

                                        Forms\Components\Textarea::make('hero_description_en')
                                            ->label('Hero Description (English)')
                                            ->rows(3)
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('hero_description_en')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('hero_description_en', $state)),

                                        Forms\Components\TextInput::make('hero_tagline_am')
                                            ->label('Hero Tagline (አማርኛ)')
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('hero_tagline_am')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('hero_tagline_am', $state)),
                                    ]),

                                Section::make('Portal Statistics')
                                    ->schema([
                                        Repeater::make('stats')
                                            ->schema([
                                                Forms\Components\TextInput::make('label_am')->label('Label (አማርኛ)')->required(),
                                                Forms\Components\TextInput::make('label_en')->label('Label (English)')->required(),
                                                Forms\Components\TextInput::make('value')->label('Value')->required(),
                                            ])
                                            ->columns(3)
                                            ->afterStateHydrated(fn($component, $state) => $component->state(json_decode(SiteSetting::get('stats', '[]'), true)))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('stats', json_encode($state))),
                                    ]),
                            ]),

                        // TAB 5: FEATURES
                        Tab::make('Features')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Section::make('Modules & Maintenance')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Toggle::make('enable_complaints')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state((bool) SiteSetting::get('enable_complaints', true)))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('enable_complaints', $state ? '1' : '0')),
                                                Toggle::make('enable_tips')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state((bool) SiteSetting::get('enable_tips', true)))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('enable_tips', $state ? '1' : '0')),
                                                Toggle::make('maintenance_mode')
                                                    ->afterStateHydrated(fn($component, $state) => $component->state((bool) SiteSetting::get('maintenance_mode', false)))
                                                    ->afterStateUpdated(fn($state) => SiteSetting::set('maintenance_mode', $state ? '1' : '0')),
                                            ]),
                                    ]),
                            ]),

                        // TAB 6: SEO & METADATA
                        Tab::make('SEO')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Section::make('Search Engine Optimization')
                                    ->schema([
                                        Forms\Components\TextInput::make('site_title')
                                            ->label('Site Title')
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('site_title')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('site_title', $state, 'text', 'seo')),

                                        Forms\Components\Textarea::make('meta_description')
                                            ->label('Meta Description')
                                            ->rows(3)
                                            ->afterStateHydrated(fn($component, $state) => $component->state(SiteSetting::get('meta_description')))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('meta_description', $state, 'text', 'seo')),

                                        Forms\Components\TagsInput::make('meta_keywords')
                                            ->label('Meta Keywords')
                                            ->afterStateHydrated(fn($component, $state) => $component->state(explode(',', SiteSetting::get('meta_keywords', ''))))
                                            ->afterStateUpdated(fn($state) => SiteSetting::set('meta_keywords', implode(',', $state), 'text', 'seo')),
                                    ]),
                            ]),
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->searchable(),
                Tables\Columns\TextColumn::make('value')->limit(50),
                Tables\Columns\TextColumn::make('group')->badge(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'contact' => 'Contact',
                        'appearance' => 'Appearance',
                        'hero' => 'Hero',
                        'seo' => 'SEO',
                        'features' => 'Features',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteSettings::route('/'),
            'create' => Pages\CreateSiteSetting::route('/create'),
            'edit' => Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
