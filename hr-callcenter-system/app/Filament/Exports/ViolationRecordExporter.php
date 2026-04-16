<?php

namespace App\Filament\Exports;

use App\Models\ViolationRecord;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ViolationRecordExporter extends Exporter
{
    protected static ?string $model = ViolationRecord::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('#'),
            ExportColumn::make('violator.full_name_am')->label('Violator'),
            ExportColumn::make('violationType.name_am')->label('Violation Type'),
            ExportColumn::make('violation_date')->label('Date'),
            ExportColumn::make('fine_amount')->label('Fine (ETB)'),
            ExportColumn::make('subCity.name_am')->label('Sub City'),
            ExportColumn::make('woreda.name_am')->label('Woreda'),
            ExportColumn::make('block')->label('Block'),
            ExportColumn::make('repeat_offense_count')->label('Repeat Count'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('regulation_number')->label('Regulation'),
            ExportColumn::make('reportedByUser.name')->label('Reported By'),
            ExportColumn::make('created_at')->label('Created'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = number_format($export->successful_rows);

        return "Violation records export completed: {$count} rows exported.";
    }
}
