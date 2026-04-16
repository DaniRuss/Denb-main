<?php

namespace App\Filament\Exports;

use App\Models\IncidentReport;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class IncidentReportExporter extends Exporter
{
    protected static ?string $model = IncidentReport::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('#'),
            ExportColumn::make('employee.employee_id')->label('Employee ID'),
            ExportColumn::make('employee.full_name_am')->label('Employee Name'),
            ExportColumn::make('incident_type')->label('Incident Type'),
            ExportColumn::make('incident_date')->label('Date'),
            ExportColumn::make('location')->label('Location'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('reportedBy.name')->label('Reported By'),
            ExportColumn::make('created_at')->label('Created'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = number_format($export->successful_rows);

        return "Incident reports export completed: {$count} rows exported.";
    }
}
