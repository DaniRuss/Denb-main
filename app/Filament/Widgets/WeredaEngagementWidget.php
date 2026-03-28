<?php

namespace App\Filament\Widgets;

use App\Models\Woreda;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class WeredaEngagementWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Woreda Performance Ranking (የወረዳ አፈፃፀም)';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Woreda::query()
                    ->with('subCity')
                    ->withCount([
                        'awarenessEngagements as total_sessions',
                        'volunteerTips as total_tips',
                    ])
                    // To compute total_headcount reliably, we aggregate it via a subquery or let Filament handle it.
                    // Doing a simple join or subquery:
                    ->addSelect([
                        'total_headcount' => \App\Models\AwarenessEngagement::select(DB::raw('COALESCE(SUM(headcount), 0) + COALESCE(SUM(org_headcount_male), 0) + COALESCE(SUM(org_headcount_female), 0)'))
                            ->whereColumn('woreda_id', 'woredas.id')
                    ])
                    ->orderByDesc('total_sessions')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name_en')
                    ->label('Woreda / ወረዳ')
                    ->formatStateUsing(fn ($record) => $record->name_am . ' (' . $record->name_en . ')')
                    ->searchable(['name_am', 'name_en']),
                Tables\Columns\TextColumn::make('subCity.name_am')
                    ->label('Sub-City')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_sessions')
                    ->label('Sessions Logged')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('total_headcount')
                    ->label('Citizens Reached')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_tips')
                    ->label('Tips Generated')
                    ->sortable()
                    ->color('info')
                    ->badge(),
            ])
            ->defaultPaginationPageOption(5);
    }
}
