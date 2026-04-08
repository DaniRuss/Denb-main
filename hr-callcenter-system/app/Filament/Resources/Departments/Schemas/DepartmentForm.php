<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Models\User;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Department Information')
                    ->columnSpanFull()
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name_am')
                            ->label('Name (አማርኛ)')
                            ->required()
                            ->maxLength(255),

                        \Filament\Forms\Components\TextInput::make('name_en')
                            ->label('Name (English)')
                            ->required()
                            ->maxLength(255),

                        \Filament\Forms\Components\TextInput::make('code')
                            ->label('Department Code')
                            ->required()
                            ->maxLength(50),

                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        \Filament\Forms\Components\Select::make('head_of_department_id')
                            ->label('Head of Department')
                            ->options(User::whereHas('roles', function ($query) {
                                $query->whereIn('name', ['director', 'manager']);
                            })->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ])->columns(2),
            ]);
    }
}
