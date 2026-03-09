<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Infolists\Components\Tabs::make('Employee Details')
                    ->columnSpanFull()
                    ->tabs([
                        \Filament\Infolists\Components\Tabs\Tab::make('Personal Information')
                            ->schema([
                                \Filament\Infolists\Components\Section::make('Name Details')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('first_name_am')->label('First Name (አማርኛ)'),
                                        \Filament\Infolists\Components\TextEntry::make('last_name_am')->label('Last Name (አማርኛ)'),
                                        \Filament\Infolists\Components\TextEntry::make('first_name_en')->label('First Name (English)'),
                                        \Filament\Infolists\Components\TextEntry::make('last_name_en')->label('Last Name (English)'),
                                    ])->columns(2),

                                \Filament\Infolists\Components\Section::make('Basic & Contact')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('gender'),
                                        \Filament\Infolists\Components\TextEntry::make('age'),
                                        \Filament\Infolists\Components\TextEntry::make('birth_date')->date(),
                                        \Filament\Infolists\Components\TextEntry::make('birthplace'),
                                        \Filament\Infolists\Components\TextEntry::make('email'),
                                        \Filament\Infolists\Components\TextEntry::make('phone'),
                                        \Filament\Infolists\Components\TextEntry::make('emergency_contact'),
                                    ])->columns(2),

                                \Filament\Infolists\Components\Section::make('Identification')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('national_id')->label('National ID'),
                                        \Filament\Infolists\Components\TextEntry::make('ethio_coder')->label('Ethio Coder ID'),
                                    ])->columns(2),
                            ]),

                        \Filament\Infolists\Components\Tabs\Tab::make('Location')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('subCity.name_am')->label('Sub City'),
                                \Filament\Infolists\Components\TextEntry::make('woreda.name_am')->label('Woreda'),
                                \Filament\Infolists\Components\TextEntry::make('kebele'),
                                \Filament\Infolists\Components\TextEntry::make('house_number'),
                            ])->columns(2),

                        \Filament\Infolists\Components\Tabs\Tab::make('Employment')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('employee_id')->label('Employee ID'),
                                \Filament\Infolists\Components\TextEntry::make('position'),
                                \Filament\Infolists\Components\TextEntry::make('rank')->badge(),
                                \Filament\Infolists\Components\TextEntry::make('employee_type'),
                                \Filament\Infolists\Components\TextEntry::make('salary')->money('ETB'),
                                \Filament\Infolists\Components\TextEntry::make('hire_date')->date(),
                                \Filament\Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'active' => 'success',
                                        'suspended' => 'danger',
                                        default => 'gray',
                                    }),
                                \Filament\Infolists\Components\TextEntry::make('suspension_reason')
                                    ->visible(fn($record) => $record->status === 'suspended'),
                                \Filament\Infolists\Components\TextEntry::make('suspension_date')
                                    ->date()
                                    ->visible(fn($record) => $record->status === 'suspended'),
                            ])->columns(2),

                        \Filament\Infolists\Components\Tabs\Tab::make('Education & Training')
                            ->schema([
                                \Filament\Infolists\Components\Section::make('Education')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('education_level'),
                                        \Filament\Infolists\Components\TextEntry::make('field_of_study'),
                                        \Filament\Infolists\Components\TextEntry::make('institution'),
                                    ])->columns(2),
                                \Filament\Infolists\Components\Section::make('Training')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('training_round'),
                                        \Filament\Infolists\Components\TextEntry::make('last_training_date')->date(),
                                        \Filament\Infolists\Components\TextEntry::make('training_notes')->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        \Filament\Infolists\Components\Tabs\Tab::make('Uniform Sizes')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('shirt_size'),
                                \Filament\Infolists\Components\TextEntry::make('pant_size'),
                                \Filament\Infolists\Components\TextEntry::make('shoe_size_casual'),
                                \Filament\Infolists\Components\TextEntry::make('shoe_size_leather'),
                                \Filament\Infolists\Components\TextEntry::make('hat_size'),
                                \Filament\Infolists\Components\TextEntry::make('cloth_size'),
                                \Filament\Infolists\Components\TextEntry::make('rain_cloth_size'),
                                \Filament\Infolists\Components\TextEntry::make('jacket_size'),
                                \Filament\Infolists\Components\TextEntry::make('t_shirt_size'),
                            ])->columns(3),
                    ])
            ]);
    }
}
