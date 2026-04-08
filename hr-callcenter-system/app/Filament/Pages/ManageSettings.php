<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Forms;
use Filament\Schemas;
use UnitEnum;
use BackedEnum;

class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|UnitEnum|null $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $title = 'Site Settings';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    public function fillForm(): void
    {
        $settings = SiteSetting::pluck('value', 'key')->toArray();

        // Handle JSON fields
        if (isset($settings['stats'])) {
            $settings['stats'] = json_decode($settings['stats'], true);
        }
        if (isset($settings['working_hours'])) {
            $settings['working_hours'] = json_decode($settings['working_hours'], true);
        }
        if (isset($settings['faqs'])) {
            $settings['faqs'] = json_decode($settings['faqs'], true);
        }

        // Handle Tags fields
        if (isset($settings['meta_keywords'])) {
            $settings['meta_keywords'] = explode(',', $settings['meta_keywords']);
        }

        $this->form->fill($settings);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Tabs::make('Site Settings')
                    ->tabs([
                        // TAB 1: GENERAL
                        Schemas\Components\Tabs\Tab::make('General')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Schemas\Components\Section::make('Organization Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('organization_name_am')->label('Organization Name (አማርኛ)')->required(),
                                        Forms\Components\TextInput::make('organization_name_en')->label('Organization Name (English)'),
                                        Forms\Components\TextInput::make('tagline_am')->label('Tagline (አማርኛ)'),
                                        Forms\Components\TextInput::make('tagline_en')->label('Tagline (English)'),
                                        Schemas\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('footer_text_am')->label('Footer Text (አማርኛ)'),
                                            Forms\Components\TextInput::make('footer_text_en')->label('Footer Text (English)'),
                                        ]),
                                        Forms\Components\TextInput::make('copyright_text')->label('Copyright Text'),
                                    ])->columns(2),

                                Schemas\Components\Section::make('Logos & Branding')
                                    ->schema([
                                        Schemas\Components\Grid::make(2)->schema([
                                            Forms\Components\FileUpload::make('logo_light')->label('Logo (Light Background)')->image()->disk('public')->directory('site/logo'),
                                            Forms\Components\FileUpload::make('logo_dark')->label('Logo (Dark Background)')->image()->disk('public')->directory('site/logo'),
                                            Forms\Components\FileUpload::make('favicon')->label('Favicon')->image()->disk('public')->directory('site/favicon'),
                                            Forms\Components\FileUpload::make('og_image')->label('Open Graph Image')->image()->disk('public')->directory('site/og'),
                                        ]),
                                    ]),
                            ]),

                        // TAB 2: CONTACT
                        Schemas\Components\Tabs\Tab::make('Contact')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Schemas\Components\Section::make('Contact Details')
                                    ->schema([
                                        Schemas\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('phone_primary')->label('Primary Phone'),
                                            Forms\Components\TextInput::make('phone_secondary')->label('Secondary Phone'),
                                            Forms\Components\TextInput::make('email_primary')->label('Primary Email')->email(),
                                            Forms\Components\TextInput::make('email_secondary')->label('Secondary Email')->email(),
                                        ]),
                                        Forms\Components\Textarea::make('address_am')->label('Address (አማርኛ)')->rows(3),
                                        Forms\Components\Textarea::make('address_en')->label('Address (English)')->rows(3),
                                    ]),
                                Schemas\Components\Section::make('Working Hours')
                                    ->schema([
                                        Forms\Components\Repeater::make('working_hours')
                                            ->schema([
                                                Forms\Components\TextInput::make('days_am')->required(),
                                                Forms\Components\TextInput::make('days_en')->required(),
                                                Forms\Components\TextInput::make('hours')->required(),
                                            ])->columns(3),
                                    ]),
                            ]),

                        // TAB 3: APPEARANCE
                        Schemas\Components\Tabs\Tab::make('Appearance')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Schemas\Components\Section::make('Brand Colors')
                                    ->schema([
                                        Schemas\Components\Grid::make(3)->schema([
                                            Forms\Components\ColorPicker::make('primary_color'),
                                            Forms\Components\ColorPicker::make('secondary_color'),
                                            Forms\Components\ColorPicker::make('accent_color'),
                                        ]),
                                    ]),
                            ]),

                        // TAB 4: HERO
                        Schemas\Components\Tabs\Tab::make('Hero')
                            ->icon('heroicon-o-megaphone')
                            ->schema([
                                Schemas\Components\Section::make('Hero Content')
                                    ->schema([
                                        Schemas\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('hero_title_am')->label('Hero Title (አማርኛ)'),
                                            Forms\Components\TextInput::make('hero_title_en')->label('Hero Title (English)'),
                                            Forms\Components\TextInput::make('hero_subtitle_am')->label('Hero Subtitle (አማርኛ)'),
                                            Forms\Components\TextInput::make('hero_subtitle_en')->label('Hero Subtitle (English)'),
                                        ]),
                                        Forms\Components\Textarea::make('hero_description_en')->label('Hero Description (English)')->rows(3),
                                        Forms\Components\TextInput::make('hero_tagline_am')->label('Hero Tagline (አማርኛ)'),
                                    ]),
                                Schemas\Components\Section::make('Statistics')
                                    ->schema([
                                        Forms\Components\Repeater::make('stats')
                                            ->schema([
                                                Forms\Components\TextInput::make('label_am')->required(),
                                                Forms\Components\TextInput::make('label_en')->required(),
                                                Forms\Components\TextInput::make('value')->required(),
                                            ])->columns(3),
                                    ]),
                            ]),

                        // TAB 5: SEO
                        Schemas\Components\Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Schemas\Components\Section::make('Search Engine Optimization')
                                    ->schema([
                                        Forms\Components\TextInput::make('site_title')->label('Site Title'),
                                        Forms\Components\Textarea::make('meta_description')->label('Meta Description')->rows(3),
                                        Forms\Components\TagsInput::make('meta_keywords')->label('Meta Keywords'),
                                    ]),
                            ]),

                        // TAB 6: FEATURES
                        Schemas\Components\Tabs\Tab::make('Features')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Schemas\Components\Section::make('Module Toggles')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_complaints'),
                                        Forms\Components\Toggle::make('enable_tips'),
                                        Forms\Components\Toggle::make('maintenance_mode'),
                                    ])->columns(3),
                            ]),

                        // TAB 7: FAQs
                        Schemas\Components\Tabs\Tab::make('FAQs')
                            ->icon('heroicon-o-question-mark-circle')
                            ->schema([
                                Schemas\Components\Section::make('Frequently Asked Questions')
                                    ->schema([
                                        Forms\Components\Repeater::make('faqs')
                                            ->schema([
                                                Forms\Components\TextInput::make('question_am')->label('Question (አማርኛ)')->required(),
                                                Forms\Components\TextInput::make('question_en')->label('Question (English)')->required(),
                                                Forms\Components\Textarea::make('answer_am')->label('Answer (አማርኛ)')->required()->rows(3),
                                                Forms\Components\Textarea::make('answer_en')->label('Answer (English)')->required()->rows(3),
                                            ])->columns(2)->itemLabel(fn(array $state): ?string => $state['question_en'] ?? null),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    if ($key === 'meta_keywords') {
                        $value = implode(',', $value);
                    } else {
                        $value = json_encode($value);
                    }
                }

                SiteSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) $value]
                );
            }

            Notification::make()
                ->title('Settings saved successfully!')
                ->success()
                ->send();

        } catch (Halt $exception) {
            return;
        }
    }
}
