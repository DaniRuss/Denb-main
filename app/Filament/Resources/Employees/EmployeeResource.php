<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\Pages\ViewEmployee;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Schemas\EmployeeInfolist;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use App\Filament\Resources\Employees\RelationManagers\UniformDistributionRelationManager;
use App\Models\Employee;
use App\Models\SubCity;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        if ($user->hasRole('admin')) {
            $subCityId = \App\Helpers\JurisdictionHelper::getSubCityId($user);
            $query->where('sub_city_id', $subCityId);
        } elseif ($user->hasRole('woreda_coordinator')) {
            $woredaId = \App\Helpers\JurisdictionHelper::getWoredaId($user);
            $query->where('woreda_id', $woredaId);
        }

        return $query;
    }

    public static function getMaxContentWidth(): \Filament\Support\Enums\Width|string|null
    {
        return 'full';
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UniformDistributionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'view' => ViewEmployee::route('/{record}'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::constrainToAssignedSubCity(parent::getRecordRouteBindingEloquentQuery())
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user && ($user->can('assign_shifts') || $user->can('manage_employees'));
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || (! $user->can('assign_shifts') && ! $user->can('manage_employees'))) {
            return false;
        }

        return ! static::shouldLimitToAssignedSubCity($user) || static::assignedSubCityId($user) !== null;
    }

    public static function canEdit($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || (! $user->can('assign_shifts') && ! $user->can('manage_employees'))) {
            return false;
        }

        return ! static::shouldLimitToAssignedSubCity($user)
            || (int) $record->sub_city_id === static::assignedSubCityId($user);
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || (! $user->can('assign_shifts') && ! $user->can('manage_employees'))) {
            return false;
        }

        return ! static::shouldLimitToAssignedSubCity($user)
            || (int) $record->sub_city_id === static::assignedSubCityId($user);
    }

    public static function canDeleteAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user
            && ! static::shouldLimitToAssignedSubCity($user)
            && ($user->can('assign_shifts') || $user->can('manage_employees'));
    }

    public static function shouldLimitToAssignedSubCity(?User $user = null): bool
    {
        $user ??= Auth::user();

        return (bool) $user
            && $user->hasRole('sub_city_hr')
            && ! $user->hasRole('admin');
    }

    public static function assignedSubCityId(?User $user = null): ?int
    {
        $user ??= Auth::user();

        if (! $user || blank($user->sub_city)) {
            return null;
        }

        $assignedSubCity = (string) $user->sub_city;

        if (is_numeric($assignedSubCity)) {
            return (int) $assignedSubCity;
        }

        $subCityId = SubCity::query()
            ->where('name_en', $assignedSubCity)
            ->orWhere('name_am', $assignedSubCity)
            ->value('id');

        if ($subCityId) {
            return (int) $subCityId;
        }

        $aliases = [
            'Akaky Kaliti' => 'Akaki Kaliti',
        ];

        $canonicalName = $aliases[$assignedSubCity] ?? null;

        $subCityId = $canonicalName
            ? SubCity::query()->where('name_en', $canonicalName)->value('id')
            : null;

        return $subCityId ? (int) $subCityId : null;
    }

    public static function constrainToAssignedSubCity(Builder $query, ?User $user = null): Builder
    {
        $user ??= Auth::user();

        if (! static::shouldLimitToAssignedSubCity($user)) {
            return $query;
        }

        $subCityId = static::assignedSubCityId($user);

        return $subCityId
            ? $query->where('sub_city_id', $subCityId)
            : $query->whereRaw('1 = 0');
    }
}
