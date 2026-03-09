<?php

namespace App\Filament\Resources;

use App\Models\QuarterlyReport;
use App\Models\Department;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\QuarterlyReports\Pages;

class QuarterlyReportResource extends Resource
{
    protected static ?string $model = QuarterlyReport::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Quarterly Reports';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            \Filament\Schemas\Components\Section::make('Report Identity')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('title')
                        ->label('Report Title')
                        ->required()
                        ->maxLength(255),

                    \Filament\Forms\Components\Select::make('quarter')
                        ->label('Quarter')
                        ->options([
                            'Q1' => 'Q1 (January–March)',
                            'Q2' => 'Q2 (April–June)',
                            'Q3' => 'Q3 (July–September)',
                            'Q4' => 'Q4 (October–December)',
                        ])
                        ->required(),

                    \Filament\Forms\Components\TextInput::make('year')
                        ->label('Year')
                        ->numeric()
                        ->required()
                        ->default(date('Y'))
                        ->minValue(2020)
                        ->maxValue(2099),

                    \Filament\Forms\Components\DatePicker::make('period_start')
                        ->label('Period Start'),

                    \Filament\Forms\Components\DatePicker::make('period_end')
                        ->label('Period End'),

                    \Filament\Forms\Components\Select::make('department_id')
                        ->label('Department (if specific)')
                        ->options(Department::pluck('name_en', 'id'))
                        ->nullable()
                        ->placeholder('All Departments'),
                ])->columns(3),

            \Filament\Schemas\Components\Section::make('Statistics')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('total_complaints')
                        ->label('Total Complaints')
                        ->numeric()
                        ->default(0),

                    \Filament\Forms\Components\TextInput::make('resolved_complaints')
                        ->label('Resolved Complaints')
                        ->numeric()
                        ->default(0),

                    \Filament\Forms\Components\TextInput::make('pending_complaints')
                        ->label('Pending Complaints')
                        ->numeric()
                        ->default(0),

                    \Filament\Forms\Components\TextInput::make('total_tips')
                        ->label('Total Tips Received')
                        ->numeric()
                        ->default(0),

                    \Filament\Forms\Components\TextInput::make('verified_tips')
                        ->label('Verified Tips')
                        ->numeric()
                        ->default(0),

                    \Filament\Forms\Components\TextInput::make('total_escalations')
                        ->label('Total Escalations')
                        ->numeric()
                        ->default(0),
                ])->columns(3),

            \Filament\Schemas\Components\Section::make('Report Content')
                ->schema([
                    \Filament\Forms\Components\Textarea::make('summary')
                        ->label('Executive Summary')
                        ->rows(4)
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Textarea::make('recommendations')
                        ->label('Recommendations')
                        ->rows(4)
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Select::make('prepared_by')
                        ->label('Prepared By')
                        ->options(User::pluck('name', 'id'))
                        ->nullable(),

                    \Filament\Forms\Components\Select::make('approved_by')
                        ->label('Approved By')
                        ->options(User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'director']))->pluck('name', 'id'))
                        ->nullable(),

                    \Filament\Forms\Components\Select::make('status')
                        ->label('Report Status')
                        ->options([
                            'draft' => 'Draft',
                            'under_review' => 'Under Review',
                            'approved' => 'Approved',
                            'published' => 'Published',
                        ])
                        ->default('draft')
                        ->required(),

                    \Filament\Forms\Components\FileUpload::make('report_file')
                        ->label('Attach Report File (PDF)')
                        ->directory('quarterly-reports')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(20480),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Report Title')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('quarter')
                    ->label('Quarter')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->label('Year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_complaints')
                    ->label('Complaints'),

                Tables\Columns\TextColumn::make('resolved_complaints')
                    ->label('Resolved')
                    ->color('success'),

                Tables\Columns\TextColumn::make('pending_complaints')
                    ->label('Pending')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'draft' => 'gray',
                        'under_review' => 'warning',
                        'approved' => 'success',
                        'published' => 'info',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('preparedBy.name')
                    ->label('Prepared By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('quarter')->options([
                    'Q1' => 'Q1',
                    'Q2' => 'Q2',
                    'Q3' => 'Q3',
                    'Q4' => 'Q4',
                ]),
                SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'under_review' => 'Under Review',
                    'approved' => 'Approved',
                    'published' => 'Published',
                ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\ViewAction::make(),
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
            'index' => Pages\ListQuarterlyReports::route('/'),
            'create' => Pages\CreateQuarterlyReport::route('/create'),
            'view' => Pages\ViewQuarterlyReport::route('/{record}'),
            'edit' => Pages\EditQuarterlyReport::route('/{record}/edit'),
        ];
    }
}
