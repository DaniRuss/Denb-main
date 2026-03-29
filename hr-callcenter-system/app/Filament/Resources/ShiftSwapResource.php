<?php

namespace App\Filament\Resources;

use App\Models\Employee;
use App\Models\ShiftAssignment;
use App\Models\ShiftSwap;
use App\Support\EthiopianDate;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ShiftSwapResource extends Resource
{
    protected static ?string $model = ShiftSwap::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string|\UnitEnum|null $navigationGroup = 'Shift Management';

    protected static ?string $navigationLabel = 'Shift Swap';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        $employeeOptions = Employee::query()
            ->active()
            ->orderBy('first_name_am')
            ->get()
            ->mapWithKeys(fn ($e) => [$e->id => $e->employee_id.' – '.$e->full_name_am])
            ->all();

        return $schema->schema([
            Section::make('Swap Request')
                ->schema([
                    Forms\Components\Select::make('employee_from')
                        ->label('Employee (giving shift)')
                        ->options($employeeOptions)
                        ->searchable()
                        ->required()
                        ->live(),
                    Forms\Components\Select::make('employee_to')
                        ->label('Employee (taking shift)')
                        ->options($employeeOptions)
                        ->searchable()
                        ->required()
                        ->rules([
                            fn (Get $get): \Closure => function (string $attr, $value, \Closure $fail) use ($get): void {
                                if ($value && $get('employee_from') == $value) {
                                    $fail(__('The receiving employee must be different from the one giving the shift.'));
                                }
                            },
                        ])
                        ->live(),
                    Forms\Components\Select::make('shift_assignment_id')
                        ->label('Shift Assignment')
                        ->options(function (Get $get) {
                            $empFrom = $get('employee_from');
                            if (! $empFrom) {
                                return [];
                            }
                            $today = EthiopianDate::todayGregorianInAddisAbaba();

                            return ShiftAssignment::query()
                                ->where('employee_id', $empFrom)
                                ->where('status', 'scheduled')
                                ->whereDate('assigned_date', '>=', $today)
                                ->with('shift')
                                ->orderBy('assigned_date', 'asc')
                                ->get()
                                ->mapWithKeys(fn ($a) => [$a->id => (EthiopianDate::toEcYmdAmharic($a->assigned_date) ?? EthiopianDate::toEcYmd($a->assigned_date) ?? $a->assigned_date->format('Y-m-d')).' – '.($a->shift?->name ?? '').' (Block '.$a->block.')'])
                                ->all();
                        })
                        ->required()
                        ->rules([
                            fn (Get $get): \Closure => function (string $attr, $value, \Closure $fail) use ($get): void {
                                $empTo = $get('employee_to');
                                if (! $value || ! $empTo) {
                                    return;
                                }
                                $assignment = ShiftAssignment::query()->find($value);
                                if (! $assignment) {
                                    return;
                                }
                                $conflict = ShiftAssignment::query()
                                    ->where('employee_id', $empTo)
                                    ->whereDate('assigned_date', $assignment->assigned_date)
                                    ->where('id', '!=', $value)
                                    ->exists();
                                if ($conflict) {
                                    $fail(__('The receiving employee already has a shift on this date.'));
                                }
                            },
                        ])
                        ->live(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->default('pending')
                        ->required(),
                    Forms\Components\Select::make('approved_by')
                        ->label('Approved by')
                        ->relationship('approvedBy', 'name')
                        ->searchable()
                        ->visible(fn (Get $get) => in_array($get('status'), ['approved', 'rejected'])),
                    Forms\Components\Textarea::make('reason')->maxLength(1000)->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employeeFrom.employee_id')->label('From (ID)')->sortable(),
                Tables\Columns\TextColumn::make('employeeFrom.full_name_am')->label('From'),
                Tables\Columns\TextColumn::make('employeeTo.employee_id')->label('To (ID)')->sortable(),
                Tables\Columns\TextColumn::make('employeeTo.full_name_am')->label('To'),
                Tables\Columns\TextColumn::make('shiftAssignment.assigned_date')
                    ->label(__('Shift date (Ethiopian)'))
                    ->formatStateUsing(fn ($state) => EthiopianDate::toEcYmdAmharic($state) ?? '-'),
                Tables\Columns\TextColumn::make('shiftAssignment.shift.name')->label('Shift'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('approvedBy.name')->label('Approved by')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function ($query) {
                /** @var \App\Models\User|null $user */
                $user = Auth::user();
                if ($user && ! $user->can('approve_shift_swap')) {
                    $employee = Employee::query()->where('user_id', $user->id)->first();
                    if ($employee) {
                        $query->where(function ($q) use ($employee) {
                            $q->where('employee_from', $employee->id)
                                ->orWhere('employee_to', $employee->id);
                        });
                    }
                }

                return $query;
            })
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
            ])
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ShiftSwapResource\Pages\ListShiftSwaps::route('/'),
            'create' => \App\Filament\Resources\ShiftSwapResource\Pages\CreateShiftSwap::route('/create'),
            'edit' => \App\Filament\Resources\ShiftSwapResource\Pages\EditShiftSwap::route('/{record}/edit'),
        ];
    }

    /** Shift Swap is hidden from officers; only supervisors/admins see and manage it. */
    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('approve_shift_swap');
    }

    /** Only supervisors/admins create and approve swaps; officers can only view. */
    public static function canCreate(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('approve_shift_swap');
    }

    /** Only supervisors/admins can approve or reject. */
    public static function canEdit($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('approve_shift_swap');
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->can('approve_shift_swap');
    }
}
