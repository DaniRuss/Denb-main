<?php
namespace App\Intelligence;
use App\Models\AwarenessEngagement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
class StrategyEfficiencyVisual extends ChartWidget {
    protected ?string $heading = "Strategy Efficiency";
    protected function getData(): array {
        $user = auth()->user();
        $query = AwarenessEngagement::where("status", "approved");

        if ($user->hasRole('admin')) {
            $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
            $query->where('sub_city_id', $subCityId);
        } elseif ($user->hasRole('woreda_coordinator')) {
            $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
            $query->where('woreda_id', $woredaId);
        } elseif ($user->hasRole('paramilitary')) {
            $query->where('created_by', $user->id);
        }

        $data = $query->select("engagement_type", DB::raw("SUM(COALESCE(headcount,0) + COALESCE(org_headcount_male,0) + COALESCE(org_headcount_female,0)) as total"))
            ->groupBy("engagement_type")
            ->pluck("total", "engagement_type")
            ->toArray();

        return [ "datasets" => [[ "label" => "Reach", "data" => array_values($data), 'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b'] ]], "labels" => array_keys($data) ];
    }
    protected function getType(): string { return "pie"; }
}