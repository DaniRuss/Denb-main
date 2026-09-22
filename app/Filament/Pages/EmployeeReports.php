<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\SubCity;
use App\Models\Woreda;
use App\Support\Filament\PanelAccess;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;
use BackedEnum;

class EmployeeReports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Human Resources';

    protected static ?string $navigationLabel = 'Employee Reports';

    protected static ?string $title = 'Employee Reports';

    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.pages.employee-reports';

    public ?array $data = [];

    public array $clothingSizes = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

    public array $shoeSizes = ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];

    public array $hatSizes = ['54', '55', '56'];

    public static function canAccess(): bool
    {
        return PanelAccess::allows(['view_reports', 'manage_inventory']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->form->fill([
            'group_by' => 'sub_city',
            'status' => 'active',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Ad hoc filters')
                    ->schema([
                        Forms\Components\Select::make('group_by')
                            ->label('Group by')
                            ->options([
                                'sub_city' => 'Sub City',
                                'woreda' => 'Woreda',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('sub_city_id')
                            ->label('Sub City')
                            ->options(fn (): array => SubCity::query()->orderBy('code')->pluck('name_am', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('woreda_id', null)),
                        Forms\Components\Select::make('woreda_id')
                            ->label('Woreda')
                            ->options(function (callable $get): array {
                                return Woreda::query()
                                    ->when($get('sub_city_id'), fn ($query, $subCityId) => $query->where('sub_city_id', $subCityId))
                                    ->orderBy('code')
                                    ->pluck('name_am', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                                'on_leave' => 'On Leave',
                                'retired' => 'Retired',
                                'terminated' => 'Terminated',
                            ])
                            ->placeholder('All statuses'),
                        Forms\Components\Select::make('employee_type')
                            ->label('Employee Type')
                            ->options([
                                'para_military_officer' => 'Para Military Officer',
                                'civil_employee' => 'Civil Employee',
                                'district_para_military' => 'District Para Military',
                            ])
                            ->placeholder('All types'),
                        Forms\Components\Select::make('position')
                            ->label('Job Position')
                            ->options(fn (): array => Employee::jobPositionOptions())
                            ->searchable()
                            ->placeholder('All positions'),
                    ])
                    ->columns(4),
            ])
            ->statePath('data');
    }

    public function resetFilters(): void
    {
        $this->form->fill([
            'group_by' => 'sub_city',
            'status' => 'active',
        ]);
    }

    public function getRowsProperty(): array
    {
        $employees = $this->employeeQuery()->get();
        $groups = $this->groups();

        return $groups
            ->map(function (array $group, int $index) use ($employees): array {
                $groupEmployees = $employees->filter(function (Employee $employee) use ($group): bool {
                    return $this->groupBy() === 'woreda'
                        ? (int) $employee->woreda_id === (int) $group['id']
                        : (int) $employee->sub_city_id === (int) $group['id'];
                });

                return $this->buildRow($index + 1, $group['name'], $groupEmployees);
            })
            ->filter(fn (array $row): bool => $row['total'] > 0)
            ->values()
            ->all();
    }

    public function getTotalsProperty(): array
    {
        return $this->buildRow(null, 'Total', $this->employeeQuery()->get());
    }

    public function getUniformGroupsProperty(): array
    {
        return [
            'shirt' => [
                'label' => 'Shirt',
                'field' => 'shirt_size',
                'sizes' => $this->clothingSizes,
            ],
            'pant' => [
                'label' => 'Pant',
                'field' => 'pant_size',
                'sizes' => $this->clothingSizes,
            ],
            'casual_shoe' => [
                'label' => 'Casual Shoe',
                'field' => 'shoe_size_casual',
                'sizes' => $this->shoeSizes,
            ],
            'leather_shoe' => [
                'label' => 'Leather Shoe',
                'field' => 'shoe_size_leather',
                'sizes' => $this->shoeSizes,
            ],
            'hat' => [
                'label' => 'Hat',
                'field' => 'hat_size',
                'sizes' => $this->hatSizes,
            ],
            'cloth' => [
                'label' => 'Cloth',
                'field' => 'cloth_size',
                'sizes' => $this->clothingSizes,
            ],
            'rain_cloth' => [
                'label' => 'Rain Cloth',
                'field' => 'rain_cloth_size',
                'sizes' => $this->clothingSizes,
            ],
            'jacket' => [
                'label' => 'Jacket',
                'field' => 'jacket_size',
                'sizes' => $this->clothingSizes,
            ],
            't_shirt' => [
                'label' => 'T-Shirt',
                'field' => 't_shirt_size',
                'sizes' => $this->clothingSizes,
            ],
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $filename = 'employee-report-' . now('Africa/Addis_Ababa')->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $this->csvHeaders());

            foreach ($this->rows as $row) {
                fputcsv($handle, $this->csvRow($row));
            }

            fputcsv($handle, $this->csvRow($this->totals));
            fclose($handle);
        }, $filename);
    }

    protected function employeeQuery(): Builder
    {
        $filters = $this->data ?? [];

        return Employee::query()
            ->with(['subCity', 'woreda'])
            ->when($filters['sub_city_id'] ?? null, fn ($query, $id) => $query->where('sub_city_id', $id))
            ->when($filters['woreda_id'] ?? null, fn ($query, $id) => $query->where('woreda_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['employee_type'] ?? null, fn ($query, $type) => $query->where('employee_type', $type))
            ->when($filters['position'] ?? null, fn ($query, $position) => $query->where('position', $position));
    }

    protected function groups(): Collection
    {
        $filters = $this->data ?? [];

        if ($this->groupBy() === 'woreda') {
            return Woreda::query()
                ->with('subCity')
                ->when($filters['sub_city_id'] ?? null, fn ($query, $id) => $query->where('sub_city_id', $id))
                ->when($filters['woreda_id'] ?? null, fn ($query, $id) => $query->where('id', $id))
                ->orderBy('sub_city_id')
                ->orderBy('code')
                ->get()
                ->map(fn (Woreda $woreda): array => [
                    'id' => $woreda->id,
                    'name' => trim(($woreda->subCity?->name_am ? $woreda->subCity->name_am . ' - ' : '') . $woreda->name_am),
                ]);
        }

        return SubCity::query()
            ->when($filters['sub_city_id'] ?? null, fn ($query, $id) => $query->where('id', $id))
            ->orderBy('code')
            ->get()
            ->map(fn (SubCity $subCity): array => [
                'id' => $subCity->id,
                'name' => $subCity->name_am,
            ]);
    }

    protected function buildRow(?int $number, string $name, Collection $employees): array
    {
        $uniforms = [];

        foreach ($this->uniformGroups as $key => $group) {
            $uniforms[$key] = $this->countBySize($employees, $group['field'], $group['sizes']);
        }

        return [
            'number' => $number,
            'name' => $name,
            'total' => $employees->count(),
            'uniforms' => $uniforms,
        ];
    }

    protected function countBySize(Collection $employees, string $field, array $sizes): array
    {
        $counts = [];

        foreach ($sizes as $size) {
            $counts[$size] = $employees
                ->filter(fn (Employee $employee): bool => strtoupper(trim((string) $employee->{$field})) === strtoupper((string) $size))
                ->count();
        }

        $counts['total'] = array_sum($counts);

        return $counts;
    }

    protected function groupBy(): string
    {
        return (string) ($this->data['group_by'] ?? 'sub_city');
    }

    protected function csvHeaders(): array
    {
        $headers = ['No', 'Group', 'Employees'];

        foreach ($this->uniformGroups as $group) {
            foreach ($group['sizes'] as $size) {
                $headers[] = $group['label'] . ' ' . $size;
            }

            $headers[] = $group['label'] . ' Total';
        }

        return $headers;
    }

    protected function csvRow(array $row): array
    {
        $values = [$row['number'], $row['name'], $row['total']];

        foreach ($this->uniformGroups as $key => $group) {
            foreach ($group['sizes'] as $size) {
                $values[] = $row['uniforms'][$key][$size];
            }

            $values[] = $row['uniforms'][$key]['total'];
        }

        return $values;
    }
}
