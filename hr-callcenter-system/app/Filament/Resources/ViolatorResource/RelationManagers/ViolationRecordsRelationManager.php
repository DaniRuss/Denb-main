<?php

namespace App\Filament\Resources\ViolatorResource\RelationManagers;

use App\Models\SubCity;
use App\Models\User;
use App\Models\ViolationType;
use App\Models\Woreda;
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
use Illuminate\Database\Eloquent\Builder;

class ViolationRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'violationRecords';

    protected static ?string $recordTitleAttribute = 'violation_date';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('violation_type_id')
                ->label(app()->getLocale() === 'am' ? 'የጥፋት አይነት' : 'Violation Type')
                ->options(ViolationType::active()->get()->mapWithKeys(fn ($v) => [$v->id => $v->display_name]))
                ->searchable()
                ->required(),
            Forms\Components\DatePicker::make('violation_date')
                ->label(app()->getLocale() === 'am' ? 'ቀን' : 'Date')
                ->ethiopic()
                ->firstDayOfWeek(1)
                ->closeOnDateSelection()
                ->default(now())
                ->required(),
            Forms\Components\Select::make('sub_city_id')
                ->label(app()->getLocale() === 'am' ? 'ክፍለ ከተማ' : 'Sub City')
                ->options(SubCity::pluck('name_am', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('woreda_id', null)),
            Forms\Components\Select::make('woreda_id')
                ->label(app()->getLocale() === 'am' ? 'ወረዳ' : 'Woreda')
                ->options(fn (Get $get) => $get('sub_city_id')
                    ? Woreda::where('sub_city_id', $get('sub_city_id'))->pluck('name_am', 'id')
                    : []
                )
                ->searchable(),
            Forms\Components\TextInput::make('block')
                ->label(app()->getLocale() === 'am' ? '��ሎክ' : 'Block'),
            Forms\Components\TextInput::make('specific_location')
                ->label(app()->getLocale() === 'am' ? 'ልዩ ቦታ' : 'Specific Location'),
            Forms\Components\Select::make('status')
                ->label(app()->getLocale() === 'am' ? 'ሁኔታ' : 'Status')
                ->options([
                    'open' => app()->getLocale() === 'am' ? 'ጅምር' : 'Open',
                    'warning_issued' => app()->getLocale() === 'am' ? 'ማስጠንቀቂያ' : 'Warning Issued',
                    'penalty_issued' => app()->getLocale() === 'am' ? 'ቅጣት ተሰጥቷል' : 'Penalty Issued',
                    'payment_pending' => app()->getLocale() === 'am' ? 'ክፍያ በመጠበቅ' : 'Payment Pending',
                    'paid' => app()->getLocale() === 'am' ? 'ተከፍሏል' : 'Paid',
                    'court_filed' => app()->getLocale() === 'am' ? 'ክስ ቀርቧል' : 'Court Filed',
                    'closed' => app()->getLocale() === 'am' ? 'ያለቀ' : 'Closed',
                ])
                ->default('open')
                ->required(),
            Forms\Components\Select::make('reported_by')
                ->label(app()->getLocale() === 'am' ? 'ያሳወቀው ኦፊሰር' : 'Reported By')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->default(auth()->id())
                ->required(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                if (! $user || $user->hasRole('admin') || $user->can('manage_penalty_action')) {
                    return $query;
                }

                if ($user->hasRole('officer')) {
                    $query->where('reported_by', $user->id);
                }

                return $query
                    ->when($user->sub_city, fn (Builder $q) => $q->where('sub_city_id', $user->sub_city))
                    ->when($user->woreda, fn (Builder $q) => $q->where('woreda_id', $user->woreda));
            })
            ->columns([
                Tables\Columns\TextColumn::make('violationType.name_am')
                    ->label(app()->getLocale() === 'am' ? 'አይነት' : 'Type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('violation_date')
                    ->label(app()->getLocale() === 'am' ? 'ቀን' : 'Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fine_amount')
                    ->label(app()->getLocale() === 'am' ? 'ቅጣት' : 'Fine')
                    ->money('ETB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'secondary',
                        'warning_issued' => 'warning',
                        'penalty_issued' => 'info',
                        'payment_pending' => 'warning',
                        'paid' => 'success',
                        'court_filed' => 'danger',
                        'closed' => 'success',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('repeat_offense_count')
                    ->label(app()->getLocale() === 'am' ? 'ድግግሞሽ' : 'Repeat'),
            ])
            ->defaultSort('violation_date', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
