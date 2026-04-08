<?php

namespace App\Filament\Resources;

use App\Models\Escalation;
use App\Models\Complaint;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Escalations\Pages;

class EscalationResource extends Resource
{
    protected static ?string $model = Escalation::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';
    protected static string|\UnitEnum|null $navigationGroup = 'Case Management';
    protected static ?string $navigationLabel = 'Escalations';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make('Escalation Details')
                ->schema([
                    \Filament\Forms\Components\Select::make('complaint_id')
                        ->label('Complaint')
                        ->options(Complaint::pluck('ticket_number', 'id'))
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('escalated_by')
                        ->label('Escalated By')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    \Filament\Forms\Components\Select::make('escalated_to')
                        ->label('Escalated To')
                        ->options(User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'supervisor', 'director']))->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    \Filament\Forms\Components\Select::make('level')
                        ->label('Escalation Level')
                        ->options([
                            '1' => 'Level 1 — Team Lead',
                            '2' => 'Level 2 — Supervisor',
                            '3' => 'Level 3 — Director',
                            '4' => 'Level 4 — Commissioner',
                        ])
                        ->required(),

                    \Filament\Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'open' => 'Open',
                            'in_review' => 'In Review',
                            'resolved' => 'Resolved',
                            'closed' => 'Closed',
                        ])
                        ->default('open')
                        ->required(),

                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Reason for Escalation')
                        ->required()
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Additional Notes')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('complaint.ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('escalatedBy.name')
                    ->label('Escalated By'),

                Tables\Columns\TextColumn::make('escalatedTo.name')
                    ->label('Escalated To'),

                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->formatStateUsing(fn($state) => "Level $state"),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'open' => 'danger',
                        'in_review' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Escalated On')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'open' => 'Open',
                    'in_review' => 'In Review',
                    'resolved' => 'Resolved',
                    'closed' => 'Closed',
                ]),
                SelectFilter::make('level')->options([
                    '1' => 'Level 1',
                    '2' => 'Level 2',
                    '3' => 'Level 3',
                    '4' => 'Level 4',
                ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEscalations::route('/'),
            'create' => Pages\CreateEscalation::route('/create'),
            'edit' => Pages\EditEscalation::route('/{record}/edit'),
        ];
    }
}
