<?php

namespace App\Filament\Resources;

use App\Models\CaseAssignment;
use App\Models\Complaint;
use App\Models\Officer;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Filament\Resources\CaseAssignments\Pages;

class CaseAssignmentResource extends Resource
{
    protected static ?string $model = CaseAssignment::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string|\UnitEnum|null $navigationGroup = 'Case Management';
    protected static ?string $navigationLabel = 'Case Assignments';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make('Assignment Details')
                ->schema([
                    \Filament\Forms\Components\Select::make('complaint_id')
                        ->label('Complaint')
                        ->options(Complaint::pluck('ticket_number', 'id'))
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('officer_id')
                        ->label('Assign to Officer')
                        ->options(Officer::with('user')->get()->pluck('user.name', 'id'))
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('assigned_by')
                        ->label('Assigned By')
                        ->options(User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'supervisor']))->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    \Filament\Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'assigned' => 'Assigned',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'reassigned' => 'Reassigned',
                        ])
                        ->default('assigned')
                        ->required(),

                    \Filament\Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date'),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Assignment Notes')
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

                Tables\Columns\TextColumn::make('complaint.complaint_type')
                    ->label('Complaint Type')
                    ->formatStateUsing(fn($state) => ucwords(str_replace('_', ' ', $state ?? ''))),

                Tables\Columns\TextColumn::make('officer.user.name')
                    ->label('Assigned Officer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->label('Assigned By'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'assigned' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'reassigned' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Assigned On')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListCaseAssignments::route('/'),
            'create' => Pages\CreateCaseAssignment::route('/create'),
            'edit' => Pages\EditCaseAssignment::route('/{record}/edit'),
        ];
    }
}
