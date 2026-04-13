<?php

namespace App\Filament\Resources\ViolationRecordResource\RelationManagers;

use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WarningLettersRelationManager extends RelationManager
{
    protected static string $relationship = 'warningLetters';

    protected static ?string $recordTitleAttribute = 'reference_number';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('reference_number')
                ->label(app()->getLocale() === 'am' ? 'ቁጥር' : 'Reference Number')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(80),
            Forms\Components\Select::make('warning_type')
                ->label(app()->getLocale() === 'am' ? 'የማስጠንቀቂያ አይነት' : 'Warning Type')
                ->options([
                    'three_day' => app()->getLocale() === 'am' ? 'የ3 ቀን ማስጠንቀቂያ' : '3-Day Warning',
                    'twenty_four_hour' => app()->getLocale() === 'am' ? 'የ24 ሰዓት ማስጠንቀቂያ' : '24-Hour Warning',
                ])
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $issuedDate = $get('issued_date');
                    if ($issuedDate && $state) {
                        $deadline = match ($state) {
                            'three_day' => \Carbon\Carbon::parse($issuedDate)->addDays(3),
                            'twenty_four_hour' => \Carbon\Carbon::parse($issuedDate)->addHours(24),
                            default => null,
                        };
                        if ($deadline) {
                            $set('deadline', $deadline->toDateTimeString());
                        }
                    }
                }),
            Forms\Components\DatePicker::make('issued_date')
                ->label(app()->getLocale() === 'am' ? 'ቀን' : 'Issued Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->default(now())
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    $type = $get('warning_type');
                    if ($state && $type) {
                        $deadline = match ($type) {
                            'three_day' => \Carbon\Carbon::parse($state)->addDays(3),
                            'twenty_four_hour' => \Carbon\Carbon::parse($state)->addHours(24),
                            default => null,
                        };
                        if ($deadline) {
                            $set('deadline', $deadline->toDateTimeString());
                        }
                    }
                }),
            Forms\Components\DateTimePicker::make('deadline')
                ->label(app()->getLocale() === 'am' ? 'የገደብ ጊዜ' : 'Deadline')
                ->required()
                ->seconds(false),
            Forms\Components\TextInput::make('regulation_number')
                ->label(app()->getLocale() === 'am' ? 'ደንብ ቁጥር' : 'Regulation Number')
                ->default(fn (RelationManager $livewire) => $livewire->ownerRecord->regulation_number),
            Forms\Components\TextInput::make('article')
                ->label(app()->getLocale() === 'am' ? 'አንቀጽ' : 'Article')
                ->default(fn (RelationManager $livewire) => $livewire->ownerRecord->article),
            Forms\Components\TextInput::make('sub_article')
                ->label(app()->getLocale() === 'am' ? 'ንዑስ አንቀጽ' : 'Sub Article')
                ->default(fn (RelationManager $livewire) => $livewire->ownerRecord->sub_article),
            Forms\Components\Select::make('delivery_method')
                ->label(app()->getLocale() === 'am' ? 'የማስረከቢያ መንገድ' : 'Delivery Method')
                ->options([
                    'in_person' => app()->getLocale() === 'am' ? 'በአካል' : 'In Person',
                    'posted' => app()->getLocale() === 'am' ? 'በመለጠፍ' : 'Posted',
                ])
                ->default('in_person')
                ->required(),
            Forms\Components\Toggle::make('violator_accepted')
                ->label(app()->getLocale() === 'am' ? 'ተቀብሏል' : 'Violator Accepted')
                ->default(true),
            Forms\Components\Toggle::make('complied')
                ->label(app()->getLocale() === 'am' ? 'ተፈጻሚ ሆኗል' : 'Complied')
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    if ($state) {
                        $set('complied_at', now()->toDateTimeString());
                    } else {
                        $set('complied_at', null);
                    }
                }),
            Forms\Components\DateTimePicker::make('complied_at')
                ->label(app()->getLocale() === 'am' ? 'የተፈጸመበት ጊዜ' : 'Complied At')
                ->seconds(false)
                ->visible(fn (Get $get) => $get('complied')),
            Forms\Components\Toggle::make('escalated_to_task_force')
                ->label(app()->getLocale() === 'am' ? 'ወደ ግብረ ኃይል ተላልፏል' : 'Escalated to Task Force')
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    if ($state) {
                        $set('escalation_date', now()->toDateString());
                    } else {
                        $set('escalation_date', null);
                    }
                }),
            Forms\Components\DatePicker::make('escalation_date')
                ->label(app()->getLocale() === 'am' ? 'የተላለፈበት ቀን' : 'Escalation Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->visible(fn (Get $get) => $get('escalated_to_task_force')),
            Forms\Components\Select::make('issued_by')
                ->label(app()->getLocale() === 'am' ? 'ኦፊሰር 1' : 'Issued By')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->default(auth()->id())
                ->required(),
            Forms\Components\Select::make('issued_by_officer_2')
                ->label(app()->getLocale() === 'am' ? 'ኦፊሰር 2' : 'Officer 2')
                ->options(User::pluck('name', 'id'))
                ->searchable(),
            Forms\Components\Textarea::make('notes')
                ->label(app()->getLocale() === 'am' ? 'ማስታወሻ' : 'Notes')
                ->maxLength(5000)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label(app()->getLocale() === 'am' ? 'ቁጥር' : 'Ref #')
                    ->searchable(),
                Tables\Columns\TextColumn::make('warning_type')
                    ->label(app()->getLocale() === 'am' ? 'አይነት' : 'Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'three_day' => app()->getLocale() === 'am' ? 'የ3 ቀን' : '3-Day',
                        'twenty_four_hour' => app()->getLocale() === 'am' ? 'የ24 ሰዓት' : '24-Hour',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'three_day' => 'warning',
                        'twenty_four_hour' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('issued_date')
                    ->label(app()->getLocale() === 'am' ? 'ቀን' : 'Issued')
                    ->date(),
                Tables\Columns\TextColumn::make('deadline')
                    ->label(app()->getLocale() === 'am' ? 'ገደብ' : 'Deadline')
                    ->dateTime(),
                Tables\Columns\IconColumn::make('complied')
                    ->label(app()->getLocale() === 'am' ? 'ተፈጻሚ' : 'Complied')
                    ->boolean(),
                Tables\Columns\IconColumn::make('escalated_to_task_force')
                    ->label(app()->getLocale() === 'am' ? 'ግብረ ኃይል' : 'Task Force')
                    ->boolean()
                    ->trueColor('danger'),
            ])
            ->defaultSort('issued_date', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label(app()->getLocale() === 'am' ? 'ማስጠንቀቂያ ስጥ' : 'Issue Warning')
                    ->visible(fn () => auth()->user()?->hasRole('admin')
                        || auth()->user()?->hasRole('officer')
                        || auth()->user()?->can('issue_warning_letters')
                        || auth()->user()?->can('manage_penalty_action')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin')
                        || auth()->user()?->hasRole('supervisor')
                        || auth()->user()?->can('manage_penalty_action')
                        || auth()->user()?->can('escalate_to_task_force')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin')
                        || auth()->user()?->can('manage_penalty_action')),
            ]);
    }
}
