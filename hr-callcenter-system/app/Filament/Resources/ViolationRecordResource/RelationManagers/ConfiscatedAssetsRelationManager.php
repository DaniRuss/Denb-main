<?php

namespace App\Filament\Resources\ViolationRecordResource\RelationManagers;

use App\Models\SubCity;
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

class ConfiscatedAssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'confiscatedAssets';

    protected static ?string $recordTitleAttribute = 'description';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('description')
                ->label(app()->getLocale() === 'am' ? 'የንብረት ዝርዝር' : 'Asset Description')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('quantity')
                ->label(app()->getLocale() === 'am' ? 'ብዛት' : 'Quantity')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required(),
            Forms\Components\TextInput::make('unit')
                ->label(app()->getLocale() === 'am' ? 'የመለኪያ' : 'Unit')
                ->maxLength(30),
            Forms\Components\Toggle::make('is_perishable')
                ->label(app()->getLocale() === 'am' ? 'የሚበላሽ' : 'Perishable')
                ->default(false),
            Forms\Components\TextInput::make('seizure_receipt_number')
                ->label(app()->getLocale() === 'am' ? 'የውረሳ ደረሰኝ ቁ.' : 'Seizure Receipt #')
                ->maxLength(50),
            Forms\Components\DatePicker::make('seized_date')
                ->label(app()->getLocale() === 'am' ? 'የተወረሰ��ት ቀን' : 'Seized Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->default(now())
                ->required(),
            Forms\Components\Select::make('seized_by')
                ->label(app()->getLocale() === 'am' ? 'ያወረሰው ኦፊሰር' : 'Seized By')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->default(auth()->id())
                ->required(),
            Forms\Components\Select::make('status')
                ->label(app()->getLocale() === 'am' ? 'ሁኔታ' : 'Status')
                ->options([
                    'seized' => app()->getLocale() === 'am' ? 'ተወርሷል' : 'Seized',
                    'handed_over' => app()->getLocale() === 'am' ? 'ተረክቧል' : 'Handed Over',
                    'estimated' => app()->getLocale() === 'am' ? 'ግምት ተሰጥቷል' : 'Estimated',
                    'transferred' => app()->getLocale() === 'am' ? 'ተዛውሯል' : 'Transferred',
                    'sold' => app()->getLocale() === 'am' ? 'ተሸጧል' : 'Sold',
                    'disposed' => app()->getLocale() === 'am' ? 'ተወግዷል' : 'Disposed',
                ])
                ->default('seized')
                ->required()
                ->live(),

            // Handover fields
            Forms\Components\DatePicker::make('handover_date')
                ->label(app()->getLocale() === 'am' ? 'የተረከቡበት ቀን' : 'Handover Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->visible(fn (Get $get) => in_array($get('status'), ['handed_over', 'estimated', 'transferred', 'sold', 'disposed'])),
            Forms\Components\Select::make('received_by')
                ->label(app()->getLocale() === 'am' ? 'ተረካቢ' : 'Received By')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => in_array($get('status'), ['handed_over', 'estimated', 'transferred', 'sold', 'disposed'])),

            Forms\Components\DatePicker::make('transfer_deadline')
                ->label(app()->getLocale() === 'am' ? 'የማስተላለፊያ ገደብ (3 ቀን)' : 'Transfer Deadline (3 days)')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->visible(fn (Get $get) => in_array($get('status'), ['handed_over', 'estimated', 'transferred', 'sold', 'disposed'])),

            // Estimation fields
            Forms\Components\TextInput::make('estimated_value')
                ->label(app()->getLocale() === 'am' ? 'የዋጋ ግምት (ብር)' : 'Estimated Value (Birr)')
                ->numeric()
                ->prefix('ETB')
                ->visible(fn (Get $get) => in_array($get('status'), ['estimated', 'transferred', 'sold', 'disposed'])),
            Forms\Components\DatePicker::make('estimation_date')
                ->label(app()->getLocale() === 'am' ? 'ግምት የተሰጠበት ቀን' : 'Estimation Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->default(now())
                ->visible(fn (Get $get) => in_array($get('status'), ['estimated', 'transferred', 'sold', 'disposed'])),

            // Transfer fields
            Forms\Components\DatePicker::make('transferred_date')
                ->label(app()->getLocale() === 'am' ? 'የተዛወረበት ቀን' : 'Transfer Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->visible(fn (Get $get) => in_array($get('status'), ['transferred', 'sold'])),
            Forms\Components\Select::make('transferred_to_sub_city_id')
                ->label(app()->getLocale() === 'am' ? 'የተዛወረበት ክ/ከተማ' : 'Transferred to Sub City')
                ->options(SubCity::pluck('name_am', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => in_array($get('status'), ['transferred', 'sold'])),

            // Sale fields
            Forms\Components\DatePicker::make('sold_date')
                ->label(app()->getLocale() === 'am' ? 'የተሸጠበት ቀን' : 'Sold Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->default(now())
                ->visible(fn (Get $get) => $get('status') === 'sold'),
            Forms\Components\TextInput::make('sold_amount')
                ->label(app()->getLocale() === 'am' ? 'የሽያጭ ገንዘብ (ብር)' : 'Sold Amount (Birr)')
                ->numeric()
                ->prefix('ETB')
                ->visible(fn (Get $get) => $get('status') === 'sold')
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    if ($state) {
                        $set('authority_share', round((float) $state * 0.60, 2));
                        $set('city_finance_share', round((float) $state * 0.40, 2));
                    }
                }),
            Forms\Components\TextInput::make('authority_share')
                ->label(app()->getLocale() === 'am' ? 'የባለስልጣኑ ድርሻ (60%)' : 'Authority Share (60%)')
                ->numeric()
                ->prefix('ETB')
                ->disabled()
                ->dehydrated()
                ->visible(fn (Get $get) => $get('status') === 'sold'),
            Forms\Components\TextInput::make('city_finance_share')
                ->label(app()->getLocale() === 'am' ? 'የከተማ ፋይናንስ (40%)' : 'City Finance (40%)')
                ->numeric()
                ->prefix('ETB')
                ->disabled()
                ->dehydrated()
                ->visible(fn (Get $get) => $get('status') === 'sold'),

            // Disposal fields
            Forms\Components\DatePicker::make('disposed_date')
                ->label(app()->getLocale() === 'am' ? 'የተወገደበት ቀን' : 'Disposed Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->default(now())
                ->visible(fn (Get $get) => $get('status') === 'disposed'),
            Forms\Components\TextInput::make('disposal_reason')
                ->label(app()->getLocale() === 'am' ? 'የማስወገጃ ምክንያት' : 'Disposal Reason')
                ->visible(fn (Get $get) => $get('status') === 'disposed'),

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
                Tables\Columns\TextColumn::make('description')
                    ->label(app()->getLocale() === 'am' ? 'ዝርዝር' : 'Description')
                    ->searchable()
                    ->wrap()
                    ->limit(50),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(app()->getLocale() === 'am' ? 'ብዛት' : 'Qty'),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->label(app()->getLocale() === 'am' ? 'ግምት' : 'Value')
                    ->money('ETB'),
                Tables\Columns\TextColumn::make('seized_date')
                    ->label(app()->getLocale() === 'am' ? 'ቀን' : 'Seized')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_perishable')
                    ->label(app()->getLocale() === 'am' ? 'የሚበላሽ' : 'Perish.')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label(app()->getLocale() === 'am' ? 'ሁኔታ' : 'Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'seized' => app()->getLocale() === 'am' ? 'ተወርሷል' : 'Seized',
                        'handed_over' => app()->getLocale() === 'am' ? 'ተረክቧል' : 'Handed Over',
                        'estimated' => app()->getLocale() === 'am' ? 'ግምት' : 'Estimated',
                        'transferred' => app()->getLocale() === 'am' ? 'ተዛውሯል' : 'Transferred',
                        'sold' => app()->getLocale() === 'am' ? 'ተሸጧል' : 'Sold',
                        'disposed' => app()->getLocale() === 'am' ? 'ተወግዷል' : 'Disposed',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'seized' => 'danger',
                        'handed_over' => 'warning',
                        'estimated' => 'info',
                        'transferred' => 'info',
                        'sold' => 'success',
                        'disposed' => 'secondary',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('sold_amount')
                    ->label(app()->getLocale() === 'am' ? 'ሽያጭ' : 'Sold')
                    ->money('ETB')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('seized_date', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label(app()->getLocale() === 'am' ? 'ንብረት ውረስ' : 'Seize Asset')
                    ->visible(fn () => auth()->user()?->hasRole('admin')
                        || auth()->user()?->hasRole('officer')
                        || auth()->user()?->can('seize_assets')
                        || auth()->user()?->can('manage_penalty_action')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin')
                        || auth()->user()?->hasRole('supervisor')
                        || auth()->user()?->can('manage_confiscated_assets')
                        || auth()->user()?->can('manage_penalty_action')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('admin')
                        || auth()->user()?->can('manage_penalty_action')),
            ]);
    }
}
