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
                        ->nullable(),

                    \Filament\Forms\Components\Select::make('caseable_type')
                        ->label('Case Type')
                        ->options([
                            'App\Models\Complaint' => 'Complaint',
                            'App\Models\Tip' => 'Tip',
                        ])
                        ->required()
                        ->reactive(),

                    \Filament\Forms\Components\Select::make('caseable_id')
                        ->label('Case ID')
                        ->options(function (callable $get) {
                            $type = $get('caseable_type');
                            if (!$type) return [];
                            return $type::pluck($type === 'App\Models\Complaint' ? 'ticket_number' : 'tip_number', 'id');
                        })
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('escalated_by')
                        ->label('Escalated By')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    \Filament\Forms\Components\Select::make('escalated_to')
                        ->label('Escalated To')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    \Filament\Forms\Components\TextInput::make('from_level')
                        ->numeric()
                        ->required(),

                    \Filament\Forms\Components\TextInput::make('to_level')
                        ->numeric()
                        ->required(),

                    \Filament\Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pending',
                            'reviewed' => 'Reviewed',
                            'resolved' => 'Resolved',
                        ])
                        ->default('pending')
                        ->required(),

                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Reason for Escalation')
                        ->required()
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Textarea::make('reason_details')
                        ->label('Detailed Reason')
                        ->required()
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Additional Notes')
                        ->columnSpanFull(),

                    \Filament\Forms\Components\DateTimePicker::make('escalated_at')
                        ->default(now())
                        ->required(),

                    \Filament\Forms\Components\DateTimePicker::make('responded_at'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('caseable_type')
                    ->label('Type')
                    ->formatStateUsing(fn($state) => str($state)->afterLast('\\')->toString()),

                Tables\Columns\TextColumn::make('caseable_id')
                    ->label('ID'),

                Tables\Columns\TextColumn::make('escalatedBy.name')
                    ->label('By'),

                Tables\Columns\TextColumn::make('escalatedTo.name')
                    ->label('To'),

                Tables\Columns\TextColumn::make('from_level')
                    ->label('From'),

                Tables\Columns\TextColumn::make('to_level')
                    ->label('To'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'pending' => 'danger',
                        'reviewed' => 'warning',
                        'resolved' => 'success',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('escalated_at')
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
