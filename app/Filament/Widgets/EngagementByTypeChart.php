<?php

namespace App\Filament\Widgets;

use App\Models\AwarenessEngagement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EngagementByTypeChart extends ChartWidget
{
    protected ?string $heading = 'Engagement Strategies Used (የግንዛቤ አይነቶች)';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin', 'woreda_coordinator']);
    }

    protected function getData(): array
    {
        $user = auth()->user();
        $query = AwarenessEngagement::query()
            ->select('engagement_type', DB::raw('count(*) as total'))
            ->groupBy('engagement_type');

        if ($user->hasRole('woreda_coordinator') && $user->woreda_id) {
            $query->where('woreda_id', $user->woreda_id);
        }

        $data = $query->get();

        $labels = [];
        $counts = [];

        foreach ($data as $row) {
            $formattedLabel = ucfirst(str_replace('_', ' ', $row->engagement_type));
            $labels[] = $formattedLabel;
            $counts[] = $row->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Engagements',
                    'data' => $counts,
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
