<?php

namespace App\Filament\Widgets;

use App\Models\AwarenessEngagement;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestEngagementsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        $query = AwarenessEngagement::query();

        if ($user->hasRole('admin')) {
            $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
            $query->where('sub_city_id', $subCityId);
        } elseif ($user->hasRole('woreda_coordinator')) {
            $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
            $query->where('woreda_id', $woredaId);
        } elseif ($user->hasRole('paramilitary')) {
            $query->where('created_by', $user->id);
        }

        $query->latest()->limit(10); // Show more than 5, and after filtering

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('engagement_code')->label('Code')->hidden(),
                Tables\Columns\TextColumn::make('engagement_type')->badge(),
                Tables\Columns\TextColumn::make('campaign.name_am')->label('Campaign'),
                Tables\Columns\TextColumn::make('woreda.name_am')->label('Woreda'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('session_datetime')->dateTime()->label('Date'),
            ]);
    }
}
