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

                    \Filament\Forms\Components\Select::make('assigned_by')
                        ->label('Assigned By')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),

                    \Filament\Forms\Components\Select::make('assigned_to')
                        ->label('Assigned To')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('department_id')
                        ->relationship('department', 'name_en')
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('assignment_type')
                        ->options([
                            'primary' => 'Primary',
                            'supporting' => 'Supporting',
                            'reviewer' => 'Reviewer',
                        ])
                        ->required(),

                    \Filament\Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'completed' => 'Completed',
                            'reassigned' => 'Reassigned',
                        ])
                        ->default('active')
                        ->required(),

                    \Filament\Forms\Components\DateTimePicker::make('assigned_at')
                        ->default(now())
                        ->required(),

                    \Filament\Forms\Components\DateTimePicker::make('deadline'),
                    \Filament\Forms\Components\DateTimePicker::make('completed_at'),

                    \Filament\Forms\Components\Textarea::make('assignment_notes')
                        ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To'),

                Tables\Columns\TextColumn::make('department.name_en')
                    ->label('Department'),

                Tables\Columns\TextColumn::make('assignment_type')
                    ->label('Type')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'active' => 'info',
                        'completed' => 'success',
                        'reassigned' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('deadline')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assigned_at')
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
