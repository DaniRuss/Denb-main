<?php

namespace App\Filament\Resources;

use App\Models\Officer;
use App\Models\Department;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Officers\Pages;

class OfficerResource extends Resource
{
    protected static ?string $model = Officer::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|\UnitEnum|null $navigationGroup = 'Human Resources';
    protected static ?string $navigationLabel = 'Officers';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make('Officer Details')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('badge_number')
                        ->label('Badge Number')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),

                    \Filament\Forms\Components\Select::make('user_id')
                        ->label('Linked User Account')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    \Filament\Forms\Components\Select::make('department_id')
                        ->label('Department')
                        ->options(Department::pluck('name_en', 'id'))
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\TextInput::make('rank')
                        ->label('Rank (English)')
                        ->required()
                        ->maxLength(100),

                    \Filament\Forms\Components\TextInput::make('rank_am')
                        ->label('Rank (አማርኛ)')
                        ->maxLength(100),

                    \Filament\Forms\Components\Select::make('specialization')
                        ->label('Specialization')
                        ->options([
                            'general' => 'General Law Enforcement',
                            'financial_crimes' => 'Financial Crimes',
                            'narcotics' => 'Narcotics',
                            'traffic' => 'Traffic Enforcement',
                            'cybercrime' => 'Cybercrime',
                            'investigations' => 'Criminal Investigations',
                            'administration' => 'Administration',
                        ])
                        ->nullable(),

                    \Filament\Forms\Components\TextInput::make('phone')
                        ->label('Contact Phone')
                        ->tel()
                        ->maxLength(20),

                    \Filament\Forms\Components\DatePicker::make('date_joined')
                        ->label('Date Joined'),

                    \Filament\Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'active' => 'Active',
                            'on_leave' => 'On Leave',
                            'suspended' => 'Suspended',
                            'retired' => 'Retired',
                            'transferred' => 'Transferred',
                        ])
                        ->required()
                        ->default('active'),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('badge_number')
                    ->label('Badge #')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Officer Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('rank')
                    ->label('Rank')
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name_en')
                    ->label('Department')
                    ->sortable(),

                Tables\Columns\TextColumn::make('specialization')
                    ->label('Specialization')
                    ->formatStateUsing(fn($state) => ucwords(str_replace('_', ' ', $state ?? '')))
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'active' => 'success',
                        'on_leave' => 'warning',
                        'suspended' => 'danger',
                        'retired', 'transferred' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('date_joined')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::pluck('name_en', 'id')),

                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'on_leave' => 'On Leave',
                        'suspended' => 'Suspended',
                        'retired' => 'Retired',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfficers::route('/'),
            'create' => Pages\CreateOfficer::route('/create'),
            'view' => Pages\ViewOfficer::route('/{record}'),
            'edit' => Pages\EditOfficer::route('/{record}/edit'),
        ];
    }
}
