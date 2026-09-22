<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveRequestResource\Pages;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\SubCity;
use App\Models\User;
use App\Models\Woreda;
use App\Support\EthiopianDate;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-date-range';

    protected static string|\UnitEnum|null $navigationGroup = 'Shift Management';

    protected static ?string $navigationLabel = 'Leave Management';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $query = parent::getEloquentQuery()->with(['employee.woreda', 'reviewer']);

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (! $user->hasAnyRole(['admin', 'supervisor'])) {
            $employee = Employee::query()->where('user_id', $user->id)->first();

            return $employee ? $query->where('employee_id', $employee->id) : $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('supervisor')) {
            $scope = static::resolveSupervisorScope($user);

            if (! $scope) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('employee', function (Builder $employeeQuery) use ($scope) {
                $employeeQuery
                    ->when($scope['exclude_employee_id'], fn ($q, $id) => $q->where('id', '!=', $id))
                    ->when($scope['sub_city_id'], fn ($q, $id) => $q->where('sub_city_id', $id));

                if (! empty($scope['woreda_ids'])) {
                    $employeeQuery->whereIn('woreda_id', $scope['woreda_ids']);
                }
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $employee = $user instanceof User
            ? Employee::query()->where('user_id', $user->id)->first()
            : null;
        return $schema->schema([
            Section::make('Leave Request')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('Employee')
                        ->options(function () {
                            /** @var \App\Models\User|null $user */
                            $user = Auth::user();
                            $employee = $user instanceof User
                                ? Employee::query()->where('user_id', $user->id)->first()
                                : null;

                            $query = Employee::query()->orderBy('first_name_am');

                            if ($user && static::isSelfServiceUser($user)) {
                                $query->where('id', $employee?->id ?? 0);
                            }

                            if ($user?->hasRole('supervisor')) {
                                $scope = static::resolveSupervisorScope($user);

                                if ($scope) {
                                    $query
                                        ->when($scope['exclude_employee_id'], fn ($q, $id) => $q->where('id', '!=', $id))
                                        ->when($scope['sub_city_id'], fn ($q, $id) => $q->where('sub_city_id', $id));

                                    if (! empty($scope['woreda_ids'])) {
                                        $query->whereIn('woreda_id', $scope['woreda_ids']);
                                    }
                                } else {
                                    $query->whereRaw('1 = 0');
                                }
                            }

                            return $query->get()
                                ->mapWithKeys(fn (Employee $employee) => [$employee->id => $employee->employee_id . ' - ' . $employee->full_name_am])
                                ->all();
                        })
                        ->default($employee?->id)
                        ->searchable()
                        ->required()
                        ->disabled(fn (): bool => static::isSelfServiceUser(Auth::user()))
                        ->dehydrated(),

                    Forms\Components\Select::make('leave_type')
                        ->label('Leave Type')
                        ->options([
                            'annual' => 'Annual Leave',
                            'sick' => 'Sick Leave',
                            'family' => 'Family Leave',
                            'emergency' => 'Emergency Leave',
                            'training' => 'Training Leave',
                            'other' => 'Other',
                        ])
                        ->default('annual')
                        ->required(),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->ethiopic()
                        ->firstDayOfWeek(1)
                        ->closeOnDateSelection()
                        ->required(),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('End Date')
                        ->ethiopic()
                        ->firstDayOfWeek(1)
                        ->closeOnDateSelection()
                        ->required(),

                    Forms\Components\Textarea::make('reason')
                        ->label('Reason')
                        ->rows(4)
                        ->maxLength(3000)
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Supervisor Review')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            LeaveRequest::STATUS_PENDING => 'Pending',
                            LeaveRequest::STATUS_APPROVED => 'Approved',
                            LeaveRequest::STATUS_REJECTED => 'Rejected',
                            LeaveRequest::STATUS_CANCELLED => 'Cancelled',
                        ])
                        ->default(LeaveRequest::STATUS_PENDING)
                        ->required()
                        ->disabled(fn (): bool => static::isSelfServiceUser(Auth::user()))
                        ->dehydrated(),

                    Forms\Components\Textarea::make('review_note')
                        ->label('Review Answer')
                        ->rows(3)
                        ->maxLength(3000)
                        ->disabled(fn (): bool => static::isSelfServiceUser(Auth::user()))
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->visible(fn (): bool => ! static::isSelfServiceUser(Auth::user())),
        ]);
    }

    protected static function isSelfServiceUser(?User $user): bool
    {
        return (bool) $user && ! $user->hasAnyRole(['admin', 'supervisor']);
    }

    /**
     * Supervisor leave queues should work whether the supervisor's area is
     * stored on their linked employee record or directly on the user account.
     *
     * @return array{sub_city_id: ?int, woreda_ids: array<int>, exclude_employee_id: ?int}|null
     */
    protected static function resolveSupervisorScope(?User $user): ?array
    {
        if (! $user?->hasRole('supervisor')) {
            return null;
        }

        $supervisor = Employee::query()->where('user_id', $user->id)->first();

        $subCityId = $supervisor?->sub_city_id;
        $woredaIds = $supervisor?->woreda_id ? [(int) $supervisor->woreda_id] : [];
        $excludeEmployeeId = $supervisor?->id;

        if (! $subCityId && filled($user->sub_city)) {
            $subCityId = static::findSubCityId($user->sub_city);
        }

        if ($woredaIds === [] && filled($user->woreda)) {
            $woredaIds = static::findWoredaIds($user->woreda, $subCityId);
        }

        if (! $subCityId && $woredaIds === []) {
            return null;
        }

        return [
            'sub_city_id' => $subCityId ? (int) $subCityId : null,
            'woreda_ids' => $woredaIds,
            'exclude_employee_id' => $excludeEmployeeId ? (int) $excludeEmployeeId : null,
        ];
    }

    protected static function findSubCityId(string $value): ?int
    {
        $needle = trim($value);
        $lower = mb_strtolower($needle);

        return SubCity::query()
            ->where(function ($query) use ($needle, $lower) {
                $query->where('name_en', $needle)
                    ->orWhere('name_am', $needle)
                    ->orWhereRaw('LOWER(name_en) = ?', [$lower])
                    ->orWhereRaw('LOWER(name_am) = ?', [$lower]);
            })
            ->value('id');
    }

    /**
     * @return array<int>
     */
    protected static function findWoredaIds(string $value, ?int $subCityId = null): array
    {
        $needle = trim($value);
        $lower = mb_strtolower($needle);
        $code = ctype_digit($needle) ? (int) $needle : null;

        return Woreda::query()
            ->when($subCityId, fn ($query, $id) => $query->where('sub_city_id', $id))
            ->where(function ($query) use ($needle, $lower, $code) {
                $query->where('name_en', $needle)
                    ->orWhere('name_am', $needle)
                    ->orWhereRaw('LOWER(name_en) = ?', [$lower])
                    ->orWhereRaw('LOWER(name_am) = ?', [$lower]);

                if ($code !== null) {
                    $query->orWhere('code', $code);
                }
            })
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.employee_id')
                    ->label('Paramilitary ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.full_name_am')
                    ->label('Employee')
                    ->searchable(['first_name_am', 'last_name_am']),
                Tables\Columns\TextColumn::make('leave_type')
                    ->label('Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->formatStateUsing(fn ($state) => EthiopianDate::toEcYmdAmharic($state) ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->formatStateUsing(fn ($state) => EthiopianDate::toEcYmdAmharic($state) ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        LeaveRequest::STATUS_APPROVED => 'success',
                        LeaveRequest::STATUS_REJECTED => 'danger',
                        LeaveRequest::STATUS_CANCELLED => 'gray',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Reviewed By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('review_note')
                    ->label('Answer')
                    ->limit(80)
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        LeaveRequest::STATUS_PENDING => 'Pending',
                        LeaveRequest::STATUS_APPROVED => 'Approved',
                        LeaveRequest::STATUS_REJECTED => 'Rejected',
                        LeaveRequest::STATUS_CANCELLED => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('review_note')
                            ->label('Answer')
                            ->rows(3)
                            ->maxLength(3000),
                    ])
                    ->visible(fn (LeaveRequest $record): bool => static::canReview($record))
                    ->action(function (LeaveRequest $record, array $data): void {
                        $record->update([
                            'status' => LeaveRequest::STATUS_APPROVED,
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                            'review_note' => $data['review_note'] ?? $record->review_note,
                        ]);

                        Notification::make()->title('Leave request approved.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('review_note')
                            ->label('Reason / Answer')
                            ->rows(3)
                            ->required()
                            ->maxLength(3000),
                    ])
                    ->visible(fn (LeaveRequest $record): bool => static::canReview($record))
                    ->action(function (LeaveRequest $record, array $data): void {
                        $record->update([
                            'status' => LeaveRequest::STATUS_REJECTED,
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                            'review_note' => $data['review_note'],
                        ]);

                        Notification::make()->title('Leave request rejected.')->success()->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canReview(LeaveRequest $record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || $record->status !== LeaveRequest::STATUS_PENDING) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('supervisor')) {
            return false;
        }

        $employee = $record->employee;
        $scope = static::resolveSupervisorScope($user);

        if (! $scope || ! $employee) {
            return false;
        }

        if ($scope['exclude_employee_id'] && (int) $employee->id === (int) $scope['exclude_employee_id']) {
            return false;
        }

        if (! empty($scope['woreda_ids'])) {
            return in_array((int) $employee->woreda_id, $scope['woreda_ids'], true);
        }

        return $scope['sub_city_id'] && (int) $employee->sub_city_id === (int) $scope['sub_city_id'];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'edit' => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user && (
            $user->hasAnyRole(['admin', 'supervisor', 'officer'])
            || $user->can('view_leave_requests')
            || Employee::query()->where('user_id', $user->id)->exists()
        );
    }

    public static function canCreate(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user && (
            $user->hasAnyRole(['admin', 'supervisor', 'officer'])
            || Employee::query()->where('user_id', $user->id)->exists()
        );
    }

    public static function canEdit($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || ! $record instanceof LeaveRequest) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('supervisor')) {
            return static::canReview($record) || $record->status !== LeaveRequest::STATUS_PENDING;
        }

        return ! $user->hasAnyRole(['admin', 'supervisor'])
            && $record->status === LeaveRequest::STATUS_PENDING
            && $record->employee?->user_id === $user->id;
    }

    public static function canDelete($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || ! $record instanceof LeaveRequest) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return ! $user->hasAnyRole(['admin', 'supervisor'])
            && $record->status === LeaveRequest::STATUS_PENDING
            && $record->employee?->user_id === $user->id;
    }
}
