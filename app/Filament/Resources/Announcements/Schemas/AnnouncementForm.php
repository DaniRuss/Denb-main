<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Announcement Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title_en')
                                ->label('Title (English)')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('title_am')
                                ->label('Title (Amharic)')
                                ->required()
                                ->maxLength(255),
                        ]),
                        RichEditor::make('content_en')
                            ->label('Content (English)')
                            ->required()
                            ->columnSpanFull(),
                        RichEditor::make('content_am')
                            ->label('Content (Amharic)')
                            ->required()
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            FileUpload::make('featured_image')
                                ->image()
                                ->directory('announcements')
                                ->columnSpanFull(),
                            DateTimePicker::make('publish_date')
                                ->label('Publish Date')
                                ->required(),
                            Grid::make(2)->schema([
                                Toggle::make('is_urgent')
                                    ->label('Mark as Urgent (Red Badge)')
                                    ->default(false),
                                Toggle::make('is_active')
                                    ->label('Active / Visible')
                                    ->default(true),
                            ])->columnSpan(1),
                        ]),
                    ])
            ]);
    }
}
