<?php

namespace App\Filament\Resources\IncidentReportResource\RelationManagers;

use App\Models\ActionType;
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

class FollowUpActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'followUpActions';

    protected static ?string $recordTitleAttribute = 'status';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('action_type_id')
                ->label('Action Type')
                ->options(ActionType::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'done' => 'Done',
                ])
                ->default('pending')
                ->required()
                ->live(),
            Forms\Components\DatePicker::make('due_date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection(),
            Forms\Components\DateTimePicker::make('completed_at')
                ->label(app()->getLocale() === 'am' ? 'የተጠናቀቀበት ጊዜ' : 'Completed At')
                ->seconds(false)
                ->visible(fn ($get) => $get('status') === 'done'),
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
                Tables\Columns\TextColumn::make('actionType.name')->label('Action')->searchable(),
                Tables\Columns\TextColumn::make('due_date')->date()->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'done' => 'success',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('assignedTo.name')->label('Assigned To')->toggleable(),
                Tables\Columns\TextColumn::make('completed_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()->label('Add Follow-up Action'),
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
