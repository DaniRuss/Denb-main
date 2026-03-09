<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use App\Models\SubCity;
use App\Models\Woreda;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Employee Information')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Personal Information')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Name in Amharic')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('first_name_am')
                                            ->label('First Name (አማርኛ)')
                                            ->required()
                                            ->maxLength(255),
                                        \Filament\Forms\Components\TextInput::make('last_name_am')
                                            ->label('Last Name (አማርኛ)')
                                            ->required()
                                            ->maxLength(255),
                                    ])->columns(2),

                                Section::make('Name in English')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('first_name_en')
                                            ->label('First Name (English)')
                                            ->maxLength(255),
                                        \Filament\Forms\Components\TextInput::make('last_name_en')
                                            ->label('Last Name (English)')
                                            ->maxLength(255),
                                    ])->columns(2),

                                Section::make('Basic Information')
                                    ->schema([
                                        \Filament\Forms\Components\Select::make('gender')
                                            ->options([
                                                'male' => 'Male (ወንድ)',
                                                'female' => 'Female (ሴት)',
                                            ])
                                            ->required(),
                                        \Filament\Forms\Components\TextInput::make('age')
                                            ->numeric()
                                            ->required()
                                            ->minValue(18)
                                            ->maxValue(100),
                                        \Filament\Forms\Components\DatePicker::make('birth_date')
                                            ->required()
                                            ->maxDate(now()),
                                        \Filament\Forms\Components\TextInput::make('birthplace')
                                            ->required()
                                            ->maxLength(255),
                                    ])->columns(2),

                                Section::make('Contact Information')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        \Filament\Forms\Components\TextInput::make('phone')
                                            ->required()
                                            ->tel()
                                            ->maxLength(20),
                                        \Filament\Forms\Components\TextInput::make('emergency_contact')
                                            ->tel()
                                            ->maxLength(20),
                                    ])->columns(2),

                                Section::make('Identification')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('national_id')
                                            ->label('National ID / Driver License')
                                            ->maxLength(255),
                                        \Filament\Forms\Components\TextInput::make('ethio_coder')
                                            ->label('Ethio Coder ID')
                                            ->maxLength(255),
                                    ])->columns(2),
                            ]),

                        Tab::make('Location')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                \Filament\Forms\Components\Select::make('sub_city_id')
                                    ->label('Sub City (ክፍለ ከተማ)')
                                    ->options(\App\Models\SubCity::all()->pluck('name_am', 'id'))
                                    ->required()
                                    ->reactive(),

                                \Filament\Forms\Components\Select::make('woreda_id')
                                    ->label('Woreda (ወረዳ)')
                                    ->options(function (callable $get) {
                                        $subCityId = $get('sub_city_id');
                                        if ($subCityId) {
                                            return \App\Models\Woreda::where('sub_city_id', $subCityId)
                                                ->pluck('name_am', 'id');
                                        }
                                        return [];
                                    })
                                    ->required(),

                                \Filament\Forms\Components\TextInput::make('kebele')
                                    ->label('Kebele (ቀበሌ)')
                                    ->maxLength(255),

                                \Filament\Forms\Components\TextInput::make('house_number')
                                    ->label('House Number (የቤት ቁጥር)')
                                    ->maxLength(255),
                            ])->columns(2),

                        Tab::make('Employment')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('employee_id')
                                    ->label('Employee ID (የሰራተኛ መለያ)')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),

                                \Filament\Forms\Components\TextInput::make('position')
                                    ->label('Position (የስራ መደብ)')
                                    ->required()
                                    ->maxLength(255),

                                \Filament\Forms\Components\Select::make('rank')
                                    ->options([
                                        'officer' => 'Officer',
                                        'senior_officer' => 'Senior Officer',
                                        'supervisor' => 'Supervisor',
                                        'manager' => 'Manager',
                                        'director' => 'Director',
                                    ]),

                                \Filament\Forms\Components\Select::make('employee_type')
                                    ->options([
                                        'permanent' => 'Permanent',
                                        'contract' => 'Contract',
                                        'temporary' => 'Temporary',
                                    ]),

                                \Filament\Forms\Components\TextInput::make('salary')
                                    ->numeric()
                                    ->prefix('ETB'),

                                \Filament\Forms\Components\DatePicker::make('hire_date')
                                    ->required(),

                                \Filament\Forms\Components\Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'on_leave' => 'On Leave',
                                        'terminated' => 'Terminated',
                                        'suspended' => 'Suspended',
                                    ])
                                    ->required()
                                    ->default('active')
                                    ->reactive(),

                                \Filament\Forms\Components\Toggle::make('is_suspended_payment')
                                    ->label('Suspend Payment?')
                                    ->visible(fn($get) => $get('status') === 'suspended'),

                                \Filament\Forms\Components\TextInput::make('suspension_reason')
                                    ->visible(fn($get) => $get('status') === 'suspended'),

                                \Filament\Forms\Components\DatePicker::make('suspension_date')
                                    ->visible(fn($get) => $get('status') === 'suspended'),

                            ])->columns(2),

                        Tab::make('Education')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                \Filament\Forms\Components\Select::make('education_level')
                                    ->options([
                                        'high_school' => 'High School',
                                        'certificate' => 'Certificate',
                                        'diploma' => 'Diploma',
                                        'degree' => 'Bachelor\'s Degree',
                                        'masters' => 'Master\'s Degree',
                                        'phd' => 'PHD',
                                    ]),
                                \Filament\Forms\Components\TextInput::make('field_of_study')
                                    ->maxLength(255),
                                \Filament\Forms\Components\TextInput::make('institution')
                                    ->label('Educational Institution')
                                    ->maxLength(255),
                            ])->columns(2),

                        Tab::make('Uniform Sizes')
                            ->icon('heroicon-o-puzzle-piece')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('shirt_size')->label('Shirt Size'),
                                \Filament\Forms\Components\TextInput::make('pant_size')->label('Pant Size'),
                                \Filament\Forms\Components\TextInput::make('shoe_size_casual')->label('Shoe Size (Casual)'),
                                \Filament\Forms\Components\TextInput::make('shoe_size_leather')->label('Shoe Size (Leather)'),
                                \Filament\Forms\Components\TextInput::make('hat_size')->label('Hat Size'),
                                \Filament\Forms\Components\TextInput::make('cloth_size')->label('Cloth Size'),
                                \Filament\Forms\Components\TextInput::make('rain_cloth_size')->label('Rain Cloth Size'),
                                \Filament\Forms\Components\TextInput::make('jacket_size')->label('Jacket Size'),
                                \Filament\Forms\Components\TextInput::make('t_shirt_size')->label('T-Shirt Size'),
                            ])->columns(3),

                        Tab::make('Training')
                            ->icon('heroicon-o-academic-cap') // or another icon
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('training_round')
                                    ->numeric(),
                                \Filament\Forms\Components\DatePicker::make('last_training_date'),
                                \Filament\Forms\Components\Textarea::make('training_notes')
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ]),
            ]);
    }
}
