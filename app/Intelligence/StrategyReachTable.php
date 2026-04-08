<?php
namespace App\Intelligence;

use App\Models\AwarenessEngagement;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class StrategyReachTable extends BaseWidget
{
    protected static ?string $heading = "Strategy Reach Analysis";
    protected static ?int $sort = 2;
    protected ?string $pollingInterval = '30s';

    public static function canView(): bool { return true; }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        // Build the raw subquery (DB::table avoids Eloquent SoftDelete scope on outer query)
        $sub = DB::table('awareness_engagements')
            ->where('status', 'approved')
            ->whereNull('deleted_at')
            ->select([
                'engagement_type',
                DB::raw("SUM(COALESCE(headcount,0) + COALESCE(org_headcount_male,0) + COALESCE(org_headcount_female,0)) as total_reach"),
                DB::raw("COUNT(*) as session_count"),
                DB::raw("MAX(id) as id"),
            ])
            ->groupBy('engagement_type');

        // Scope by role
        if ($user->hasAnyRole(['admin', 'officer'])) {
            $sub->where('sub_city_id', \App\Helpers\JurisdictionHelper::getSubCityId($user));
        } elseif ($user->hasRole('woreda_coordinator')) {
            $sub->where('woreda_id', $user->woreda_id);
        } elseif ($user->hasRole('paramilitary')) {
            $sub->where('created_by', $user->id);
        }

        // Wrap in outer Eloquent query aliased as 'awareness_engagements'
        // so Filament's secondary ORDER BY awareness_engagements.id resolves to
        // the already-aggregated MAX(id) column — satisfying MySQL strict ONLY_FULL_GROUP_BY
        $query = AwarenessEngagement::withoutGlobalScopes()
            ->fromSub($sub, 'awareness_engagements');

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('engagement_type')
                    ->label('Strategy')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'coffee_ceremony' => 'Coffee Ceremony',
                        'house_to_house'  => 'House to House',
                        'organization'    => 'Organization',
                        default           => ucfirst(str_replace('_', ' ', $state ?? '')),
                    })
                    ->color(fn ($state) => match($state) {
                        'coffee_ceremony' => 'warning',
                        'house_to_house'  => 'info',
                        'organization'    => 'success',
                        default           => 'gray',
                    }),

                Tables\Columns\TextColumn::make('session_count')
                    ->label('Sessions')
                    ->numeric()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_reach')
                    ->label('Citizens Reached')
                    ->numeric()
                    ->alignRight()
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),

                Tables\Columns\TextColumn::make('id')->hidden(),
            ])
            ->defaultSort('total_reach', 'desc')
            ->paginated(false)
            ->striped();
    }
}