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

class PenaltyReceiptRelationManager extends RelationManager
{
    protected static string $relationship = 'penaltyReceipts';

    protected static ?string $recordTitleAttribute = 'receipt_number';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('receipt_number')
                ->label(app()->getLocale() === 'am' ? 'ደረሰኝ ቁጥር' : 'Receipt Number')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),
            Forms\Components\DatePicker::make('issued_date')
                ->label(app()->getLocale() === 'am' ? 'የተሰጠበት ቀን' : 'Issued Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->default(now())
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    if ($state) {
                        $set('payment_deadline', \Carbon\Carbon::parse($state)->addDays(3)->toDateString());
                    }
                }),
            Forms\Components\TimePicker::make('issued_time')
                ->label(app()->getLocale() === 'am' ? 'ሰዓት' : 'Time')
                ->seconds(false),
            Forms\Components\TextInput::make('fine_amount')
                ->label(app()->getLocale() === 'am' ? 'የቅጣት መጠን (ብር)' : 'Fine Amount (Birr)')
                ->numeric()
                ->prefix('ETB')
                ->required()
                ->default(fn (RelationManager $livewire) => $livewire->ownerRecord->fine_amount ?? 0),
            Forms\Components\DatePicker::make('payment_deadline')
                ->label(app()->getLocale() === 'am' ? 'የክፍያ ገደብ ቀን (3 ቀን)' : 'Payment Deadline (3 days)')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->required(),
            Forms\Components\Select::make('payment_status')
                ->label(app()->getLocale() === 'am' ? 'የክፍያ ሁኔታ' : 'Payment Status')
                ->options([
                    'pending' => app()->getLocale() === 'am' ? 'በመጠበቅ' : 'Pending',
                    'paid' => app()->getLocale() === 'am' ? 'ተከፍሏል' : 'Paid',
                    'overdue' => app()->getLocale() === 'am' ? 'ጊዜ ያለፈበት' : 'Overdue',
                    'court_filed' => app()->getLocale() === 'am' ? 'ክስ ቀርቧል' : 'Court Filed',
                    'court_paid' => app()->getLocale() === 'am' ? 'በፍ/ቤት ተከፍሏል' : 'Court Paid',
                ])
                ->default('pending')
                ->required()
                ->live(),
            Forms\Components\DatePicker::make('paid_date')
                ->label(app()->getLocale() === 'am' ? 'የተከፈለበት ቀን' : 'Paid Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->visible(fn (Get $get) => in_array($get('payment_status'), ['paid', 'court_paid'])),
            Forms\Components\TextInput::make('paid_amount')
                ->label(app()->getLocale() === 'am' ? 'የተከፈለ መጠን' : 'Paid Amount')
                ->numeric()
                ->prefix('ETB')
                ->visible(fn (Get $get) => in_array($get('payment_status'), ['paid', 'court_paid'])),
            Forms\Components\Toggle::make('receipt_refused')
                ->label(app()->getLocale() === 'am' ? 'ደረሰኙን አልቀበልም ብሏል' : 'Violator Refused Receipt')
                ->live(),
            Forms\Components\Select::make('issued_by')
                ->label(app()->getLocale() === 'am' ? 'ያወጣው ኦፊሰር' : 'Issued By')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->default(auth()->id())
                ->required(),
            Forms\Components\Select::make('witness_officer_1')
                ->label(app()->getLocale() === 'am' ? 'ምስክር ኦፊሰር 1' : 'Witness Officer 1')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $get('receipt_refused')),
            Forms\Components\Select::make('witness_officer_2')
                ->label(app()->getLocale() === 'am' ? 'ምስክር ኦፊሰር 2' : 'Witness Officer 2')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $get('receipt_refused')),
            Forms\Components\Select::make('witness_officer_3')
                ->label(app()->getLocale() === 'am' ? 'ምስክር ኦፊሰር 3' : 'Witness Officer 3')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $get('receipt_refused')),
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
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label(app()->getLocale() === 'am' ? 'ደረሰኝ ቁ.' : 'Receipt #')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fine_amount')
                    ->label(app()->getLocale() === 'am' ? 'ቅጣት' : 'Fine')
                    ->money('ETB'),
                Tables\Columns\TextColumn::make('issued_date')
                    ->label(app()->getLocale() === 'am' ? 'ቀን' : 'Issued')
                    ->date(),
                Tables\Columns\TextColumn::make('payment_deadline')
                    ->label(app()->getLocale() === 'am' ? 'የክፍያ ገደብ' : 'Deadline')
                    ->date(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label(app()->getLocale() === 'am' ? 'ክፍያ' : 'Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'court_filed' => 'danger',
                        'court_paid' => 'success',
                        default => 'secondary',
                    }),
                Tables\Columns\IconColumn::make('receipt_refused')
                    ->label(app()->getLocale() === 'am' ? 'ተቀባይነት' : 'Refused')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(app()->getLocale() === 'am' ? 'ደረሰኝ ስጥ' : 'Issue Receipt')
                    ->visible(fn () => auth()->user()?->hasRole('admin')
                        || auth()->user()?->hasRole('officer')
                        || auth()->user()?->can('issue_penalty_receipts')
                        || auth()->user()?->can('manage_penalty_action')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin')
                        || auth()->user()?->hasRole('supervisor')
                        || auth()->user()?->can('manage_penalty_action')
                        || auth()->user()?->can('track_payments')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin')
                        || auth()->user()?->can('manage_penalty_action')),
            ]);
    }
}
