<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use Illuminate\Support\Facades\DB;

class ParamilitaryUniformNeeds extends BaseWidget
{
    use InteractsWithPageTable;

    protected static string $resource = ListEmployees::class;

    protected function getTablePage(): string
    {
        return ListEmployees::class;
    }

    protected function getStats(): array
    {
        $tableQuery = $this->getPageTableQuery();

        if (!$tableQuery) {
            return [];
        }

        // If on UniformInventory page, we need to manually query Employees based on virtual filters
        if ($this->getTablePage() === \App\Filament\Resources\UniformInventories\Pages\ListUniformInventories::class) {
            $filters = $this->tableFilters;
            $query = Employee::query()->active();
            
            if (filled($filters['sub_city']['value'] ?? null)) {
                $query->where('sub_city_id', $filters['sub_city']['value']);
            }
            
            if (filled($filters['woreda']['value'] ?? null)) {
                $query->where('woreda_id', $filters['woreda']['value']);
            }
            
            $tableQuery = $query;
        }

        // Clone the table query to get aggregated data for sizes
        $stats = [];

        $categories = [
            'shirt_size' => ['label' => 'Shirt Needs', 'icon' => 'heroicon-m-variable', 'color' => 'info'],
            'pant_size' => ['label' => 'Pant Needs', 'icon' => 'heroicon-m-list-bullet', 'color' => 'warning'],
            'shoe_size_casual' => ['label' => 'Casual Shoe Needs', 'icon' => 'heroicon-m-sparkles', 'color' => 'primary'],
            'shoe_size_leather' => ['label' => 'Leather Shoe Needs', 'icon' => 'heroicon-m-sparkles', 'color' => 'success'],
        ];

        foreach ($categories as $field => $config) {
            $distribution = (clone $tableQuery)
                ->select($field, DB::raw('COUNT(*) as count'))
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->groupBy($field)
                ->reorder($field)  // Clear inherited orderBy (e.g. id asc) before grouping
                ->pluck('count', $field)
                ->toArray();

            $total = array_sum($distribution);
            $breakdown = [];
            foreach ($distribution as $size => $count) {
                $breakdown[] = "{$size}: {$count}";
            }
            $breakdownStr = !empty($breakdown) ? implode(', ', $breakdown) : 'No data';

            $stats[] = Stat::make($config['label'], $total)
                ->description($breakdownStr)
                ->descriptionIcon($config['icon'])
                ->color($config['color']);
        }

        return $stats;
    }
}
