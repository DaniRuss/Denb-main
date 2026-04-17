<?php
namespace App\Intelligence;

use App\Models\AwarenessEngagement;
use App\Models\Woreda;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class AdministrativePerformanceTable extends BaseWidget
{
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = '30s';

    public static function canView(): bool { return true; }

    protected function getTableHeading(): string
    {
        $user = auth()->user();
        if ($user->hasRole('super_admin'))        return 'Administrative Performance by Sub-City';
        if ($user->hasRole('admin')) return 'Administrative Performance by Woreda';
        if ($user->hasRole('woreda_coordinator'))  return 'Block & Officer Performance (My Woreda)';
        if ($user->hasRole('paramilitary'))         return 'My Latest Engagements';
        return 'Administrative Performance';
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        // ============================================================
        // SUPER ADMIN: Sub-City level breakdown
        // ============================================================
        if ($user->hasRole('super_admin')) {
            $baseQuery = \App\Models\SubCity::query();

            return $table
                ->query(
                    $baseQuery->select(
                            'sub_cities.id',
                            'sub_cities.name_am',
                            'sub_cities.name_en',
                            DB::raw("COALESCE((
                                SELECT SUM(COALESCE(ae.headcount,0) + COALESCE(ae.org_headcount_male,0) + COALESCE(ae.org_headcount_female,0))
                                FROM awareness_engagements ae
                                WHERE ae.sub_city_id = sub_cities.id AND ae.status = 'approved' AND ae.deleted_at IS NULL
                            ), 0) as total_reach"),
                            DB::raw("COALESCE((
                                SELECT COUNT(*)
                                FROM awareness_engagements ae
                                WHERE ae.sub_city_id = sub_cities.id AND ae.status = 'approved' AND ae.deleted_at IS NULL
                            ), 0) as session_count"),
                            DB::raw("COALESCE((
                                SELECT COUNT(*)
                                FROM awareness_engagements ae
                                WHERE ae.sub_city_id = sub_cities.id AND ae.status = 'submitted' AND ae.deleted_at IS NULL
                            ), 0) as pending_count")
                        )
                )
                ->columns([
                    Tables\Columns\TextColumn::make('name_am')
                        ->label('Sub-City')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('session_count')
                        ->label('Sessions')
                        ->numeric()
                        ->alignCenter()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('pending_count')
                        ->label('Pending')
                        ->numeric()
                        ->alignCenter()
                        ->badge()
                        ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),
                    Tables\Columns\TextColumn::make('total_reach')
                        ->label('Citizens Reached')
                        ->numeric()
                        ->alignRight()
                        ->sortable()
                        ->weight(\Filament\Support\Enums\FontWeight::Bold),
                ])
                ->defaultSort('total_reach', 'desc')
                ->paginated(false)
                ->striped();
        }

        // ============================================================
        // ADMIN: Woreda-level breakdown (within their sub-city)
        // ============================================================
        if ($user->hasRole('admin')) {
            $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
            $baseQuery = Woreda::where('sub_city_id', $subCityId);

            return $table
                ->query(
                    $baseQuery->select(
                            'woredas.id',
                            'woredas.name_am',
                            DB::raw("COALESCE((
                                SELECT SUM(COALESCE(ae.headcount,0) + COALESCE(ae.org_headcount_male,0) + COALESCE(ae.org_headcount_female,0))
                                FROM awareness_engagements ae
                                WHERE ae.woreda_id = woredas.id AND ae.status = 'approved' AND ae.deleted_at IS NULL
                            ), 0) as total_reach"),
                            DB::raw("COALESCE((
                                SELECT COUNT(*)
                                FROM awareness_engagements ae
                                WHERE ae.woreda_id = woredas.id AND ae.status = 'approved' AND ae.deleted_at IS NULL
                            ), 0) as session_count"),
                            DB::raw("COALESCE((
                                SELECT COUNT(*)
                                FROM awareness_engagements ae
                                WHERE ae.woreda_id = woredas.id AND ae.status = 'submitted' AND ae.deleted_at IS NULL
                            ), 0) as pending_count")
                        )
                )
                ->columns([
                    Tables\Columns\TextColumn::make('name_am')
                        ->label('Woreda')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('session_count')
                        ->label('Sessions')
                        ->numeric()
                        ->alignCenter()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('pending_count')
                        ->label('Pending')
                        ->numeric()
                        ->alignCenter()
                        ->badge()
                        ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),
                    Tables\Columns\TextColumn::make('total_reach')
                        ->label('Citizens Reached')
                        ->numeric()
                        ->alignRight()
                        ->sortable()
                        ->weight(\Filament\Support\Enums\FontWeight::Bold),
                ])
                ->defaultSort('total_reach', 'desc')
                ->paginated(false)
                ->striped();
        }

        // ============================================================
        // WOREDA COORDINATOR: Block + Officer breakdown
        // ============================================================
        if ($user->hasRole('woreda_coordinator')) {
            $sub = DB::table('awareness_engagements')
                ->where('status', 'approved')
                ->where('woreda_id', $user->woreda_id)
                ->whereNotNull('block_number')
                ->whereNull('deleted_at')
                ->select([
                    'block_number',
                    'created_by',
                    DB::raw("SUM(COALESCE(headcount,0) + COALESCE(org_headcount_male,0) + COALESCE(org_headcount_female,0)) as total_reach"),
                    DB::raw("COUNT(*) as session_count"),
                    DB::raw("MAX(id) as id"),
                ])
                ->groupBy('block_number', 'created_by');

            $query = AwarenessEngagement::withoutGlobalScopes()
                ->fromSub($sub, 'awareness_engagements');

            return $table
                ->query($query)
                ->columns([
                    Tables\Columns\TextColumn::make('block_number')
                        ->label('Block')
                        ->badge()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('createdBy.name')
                        ->label('Officer')
                        ->searchable()
                        ->default('—'),
                    Tables\Columns\TextColumn::make('session_count')
                        ->label('Sessions')
                        ->numeric()
                        ->alignCenter(),
                    Tables\Columns\TextColumn::make('total_reach')
                        ->label('Citizens Reached')
                        ->numeric()
                        ->alignRight()
                        ->sortable()
                        ->weight(\Filament\Support\Enums\FontWeight::Bold),
                ])
                ->defaultSort('total_reach', 'desc')
                ->paginated(false)
                ->striped();
        }

        // ============================================================
        // PARAMILITARY: Latest engagements (no GROUP BY — no issue)
        // ============================================================
        return $table
            ->query(
                AwarenessEngagement::where('created_by', $user->id)
                    ->latest('session_datetime')
                    ->select([
                        'id',
                        'engagement_code',
                        'engagement_type',
                        'session_datetime',
                        'status',
                        'woreda_id',
                        'campaign_id',
                        DB::raw("COALESCE(headcount,0) + COALESCE(org_headcount_male,0) + COALESCE(org_headcount_female,0) as total_reach"),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('engagement_code')
                    ->label('Code')
                    ->copyable()
                    ->fontFamily(\Filament\Support\Enums\FontFamily::Mono)
                    ->hidden(),
                Tables\Columns\TextColumn::make('campaign.name_am')
                    ->label('Campaign')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('woreda.name_am')
                    ->label('Woreda')
                    ->default('—'),
                Tables\Columns\TextColumn::make('session_datetime')
                    ->label('Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'approved'  => 'success',
                        'submitted' => 'warning',
                        'rejected'  => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_reach')
                    ->label('Reach')
                    ->numeric()
                    ->alignRight()
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
            ])
            ->defaultSort('session_datetime', 'desc')
            ->paginated(15)
            ->striped();
    }
}