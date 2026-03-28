<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DisciplineHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'disciplineHistories';

    protected static ?string $recordTitleAttribute = 'discipline_type';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\DatePicker::make('discipline_date')
                ->label('Discipline Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->default(now())
                ->required(),

            Forms\Components\Select::make('discipline_type')
                ->label('Discipline Type')
                ->options([
                    'verbal_warning' => 'Verbal Warning',
                    'written_warning' => 'Written Warning',
                    'suspension' => 'Suspension',
                    'demotion' => 'Demotion',
                    'termination' => 'Termination',
                    'other' => 'Other',
                ])
                ->required()
                ->reactive(),

            Forms\Components\TextInput::make('duration_days')
                ->label('Duration (Days)')
                ->numeric()
                ->minValue(1)
                ->visible(fn ($get) => $get('discipline_type') === 'suspension'),

            Forms\Components\Textarea::make('description')
                ->label('Reason / Description')
                ->required()
                ->maxLength(5000)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('action_taken')
                ->label('Action Taken / Decision')
                ->maxLength(5000)
                ->columnSpanFull(),

            Forms\Components\Select::make('status')
                ->options([
                    'active' => 'Active',
                    'resolved' => 'Resolved',
                    'appealed' => 'Appealed',
                ])
                ->default('active')
                ->required(),

            Forms\Components\Select::make('recorded_by')
                ->label('Recorded By')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->default(auth()->id())
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('discipline_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discipline_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'warning',
                        'resolved' => 'success',
                        'appealed' => 'info',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->label('Recorded By')
                    ->default('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('discipline_date', 'desc')
            ->headerActions([
                CreateAction::make()->label('Add Discipline Record'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
