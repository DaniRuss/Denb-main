<?php

namespace App\Filament\Resources\IncidentReportResource\RelationManagers;

use App\Models\PenaltyType;
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

class PenaltyAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'penaltyAssignments';

    protected static ?string $recordTitleAttribute = 'status';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('penalty_type_id')
                ->label('Penalty Type')
                ->options(PenaltyType::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\DatePicker::make('assigned_date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->default(now())
                ->required(),
            Forms\Components\TextInput::make('duration_days')
                ->numeric()
                ->minValue(1),
            Forms\Components\DatePicker::make('due_date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection(),
            Forms\Components\Select::make('status')
                ->options([
                    'assigned' => 'Assigned',
                    'completed' => 'Completed',
                    'revoked' => 'Revoked',
                ])
                ->default('assigned')
                ->required(),
            Forms\Components\Select::make('assigned_to')
                ->label('Assigned To')
                ->options(User::pluck('name', 'id'))
                ->searchable(),
            Forms\Components\Select::make('assigned_by')
                ->label('Assigned By')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->default(auth()->id()),
            Forms\Components\Textarea::make('notes')
                ->maxLength(8000)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('penaltyType.name')->label('Penalty')->searchable(),
                Tables\Columns\TextColumn::make('assigned_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('due_date')->date()->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'assigned' => 'warning',
                        'completed' => 'success',
                        'revoked' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned To')->toggleable(),
            ])
            ->defaultSort('assigned_date', 'desc')
            ->headerActions([
                CreateAction::make()->label('Assign Penalty'),
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
