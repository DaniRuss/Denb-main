<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

use BackedEnum;

class EmployeeProfilePage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'My Profile';
    protected static ?int $navigationSort = -1;

    protected string $view = 'filament.pages.employee-profile';

    public ?Employee $employee = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->employee = Employee::where('user_id', $user?->id)->first();
    }

    public function getTitle(): string|Htmlable
    {
        return $this->employee
            ? ($this->employee->full_name_en ?: $this->employee->full_name_am)
            : 'My Profile';
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::FiveExtraLarge;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        return Employee::where('user_id', $user->id)->exists();
    }

    public function profileInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->employee)
            ->schema([
                Tabs::make('My Profile')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Personal Information')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Name Details')
                                    ->schema([
                                        TextEntry::make('first_name_am')->label('First Name (አማርኛ)'),
                                        TextEntry::make('last_name_am')->label('Last Name (አማርኛ)'),
                                        TextEntry::make('first_name_en')->label('First Name (English)'),
                                        TextEntry::make('last_name_en')->label('Last Name (English)'),
                                    ])
                                    ->columns(2),
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
                                    ->columns(2),
                                Section::make('Identification')
                                    ->schema([
                                        TextEntry::make('national_id')->label('National ID'),
                                        TextEntry::make('ethio_coder')->label('Ethio Coder ID'),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Location')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                TextEntry::make('subCity.name_am')->label('Sub City'),
                                TextEntry::make('woreda.name_am')->label('Woreda'),
                                TextEntry::make('kebele'),
                                TextEntry::make('house_number'),
                            ])
                            ->columns(2),
                        Tab::make('Employment')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                TextEntry::make('employee_id')->label('Employee ID'),
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
                            ])
                            ->columns(2),
                        Tab::make('Education & Training')
                            ->icon('heroicon-o-academic-cap')
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
                                    ->columns(2),
                                Section::make('Training')
                                    ->schema([
                                        TextEntry::make('training_round'),
                                        TextEntry::make('last_training_date')->date(),
                                        TextEntry::make('training_notes')->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Uniform Sizes')
                            ->icon('heroicon-o-puzzle-piece')
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
                            ->columns(3),
                    ]),
            ]);
    }
}
