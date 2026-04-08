<?php

namespace App\Filament\Widgets;

use App\Models\AwarenessEngagement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ViolationHeatmapWidget extends ChartWidget
{
    protected ?string $heading = 'Violations Addressed (የተዳሰሱ ጥሰቶች)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin', 'woreda_coordinator', 'officer']);
    }

    protected function getData(): array
    {
        $user = auth()->user();
        $query = AwarenessEngagement::query()
            ->select('violation_type', DB::raw('count(*) as total'))
            ->groupBy('violation_type');

        if ($user->hasRole('admin')) {
            $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
            $query->where('sub_city_id', $subCityId);
        } elseif ($user->hasRole('woreda_coordinator')) {
            $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
            $query->where('woreda_id', $woredaId);
        }

        $data = $query->get()->sortByDesc('total');

        $labels = [];
        $counts = [];
        $map = AwarenessEngagement::violationLabels();

        foreach ($data as $row) {
            $name = $map[$row->violation_type] ?? ucfirst(str_replace('_', ' ', $row->violation_type));
            $labels[] = $name;
            $counts[] = $row->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Interventions',
                    'data' => $counts,
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
