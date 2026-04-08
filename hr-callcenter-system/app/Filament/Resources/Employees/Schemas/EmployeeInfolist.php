<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Paramilitary Details')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Personal Information')
                            ->schema([
                                Section::make('Name Details')
                                    ->schema([
                                        TextEntry::make('first_name_am')->label('First Name (Amharic)'),
                                        TextEntry::make('last_name_am')->label('Last Name (Amharic)'),
                                        TextEntry::make('first_name_en')->label('First Name (English)'),
                                        TextEntry::make('last_name_en')->label('Last Name (English)'),
                                    ])
                                    ->columns(['default' => 2]),
                                Section::make('Basic & Contact')
                                    ->schema([
                                        TextEntry::make('gender'),
                                        TextEntry::make('age'),
                                        TextEntry::make('birth_date')->date(),
                                        TextEntry::make('birthplace'),
                                        TextEntry::make('email'),
                                        TextEntry::make('phone'),
                                        TextEntry::make('emergency_contact'),
                                    ])
                                    ->columns(['default' => 2]),
                                Section::make('Identification')
                                    ->schema([
                                        TextEntry::make('national_id')->label('National ID'),
                                        TextEntry::make('ethio_coder')->label('Ethio Coder ID'),
                                    ])
                                    ->columns(['default' => 2]),
                            ]),
                        Tab::make('Location')
                            ->schema([
                                TextEntry::make('subCity.name_am')->label('Sub City'),
                                TextEntry::make('woreda.name_am')->label('Woreda'),
                                TextEntry::make('kebele'),
                                TextEntry::make('house_number'),
                            ])
                            ->columns(['default' => 2]),
                        Tab::make('Employment')
                            ->schema([
                                TextEntry::make('employee_id')->label('Paramilitary ID'),
                                TextEntry::make('position'),
                                TextEntry::make('rank')->badge(),
                                TextEntry::make('employee_type')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'para_military_officer' => 'Para Military Officer',
                                        'civil_employee' => 'Civil Employee',
                                        'district_para_military' => 'District Para Military',
                                        default => $state,
                                    }),
                                TextEntry::make('salary')->money('ETB'),
                                TextEntry::make('hire_date')->date(),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'active' => 'success',
                                        'suspended' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('suspension_reason')
                                    ->visible(fn ($record) => $record->status === 'suspended'),
                                TextEntry::make('suspension_date')
                                    ->date()
                                    ->visible(fn ($record) => $record->status === 'suspended'),
                            ])
                            ->columns(['default' => 2]),
                        Tab::make('Education & Training')
                            ->schema([
                                Section::make('Education')
                                    ->schema([
                                        TextEntry::make('education_level')
                                            ->label('Education Level')
                                            ->formatStateUsing(fn ($state) => match ($state) {
                                                'below_12' => 'Below Grade 12',
                                                'complete_12' => 'Completed Grade 12',
                                                'certificate' => 'Certificate',
                                                'diploma' => 'Diploma',
                                                'degree' => 'Bachelor Degree',
                                                'masters' => 'Master\'s Degree',
                                                'phd' => 'PhD',
                                                default => $state,
                                            }),
                                        TextEntry::make('field_of_study'),
                                        TextEntry::make('institution'),
                                    ])
                                    ->columns(['default' => 2]),
                                Section::make('Training')
                                    ->schema([
                                        TextEntry::make('training_round'),
                                        TextEntry::make('last_training_date')->date(),
                                        TextEntry::make('training_notes')->columnSpanFull(),
                                    ])
                                    ->columns(['default' => 2]),
                            ]),
                        Tab::make('Uniform Sizes')
                            ->schema([
                                TextEntry::make('shirt_size'),
                                TextEntry::make('pant_size'),
                                TextEntry::make('shoe_size_casual'),
                                TextEntry::make('shoe_size_leather'),
                                TextEntry::make('hat_size'),
                                TextEntry::make('cloth_size'),
                                TextEntry::make('rain_cloth_size'),
                                TextEntry::make('jacket_size'),
                                TextEntry::make('t_shirt_size'),
                            ])
                            ->columns(['default' => 3]),
                        Tab::make('Equipment')
                            ->schema([
                                TextEntry::make('walkie_talkie_serial')->label('Walkie Talkie Serial'),
                                \Filament\Infolists\Components\TextEntry::make('stick_issued')
                                    ->label('Stick Issued')
                                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('other_equipment')->label('Other Equipment'),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}
