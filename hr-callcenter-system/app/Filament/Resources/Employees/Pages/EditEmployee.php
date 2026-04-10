<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Filament\Support\Enums\Width;
use Illuminate\Validation\ValidationException;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    public function getMaxContentWidth(): \Filament\Support\Enums\Width|string|null
    {
        return Width::FiveExtraLarge;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $createSystemUser = (bool) ($data['create_system_user'] ?? false);
        $userPassword = $data['user_password'] ?? null;
        $userRoles = $data['user_roles'] ?? [];
        $userUsername = $data['user_username'] ?? ($data['email'] ?? null);

        unset($data['create_system_user'], $data['user_password'], $data['user_roles'], $data['user_username']);

        $record->update($data);

        $user = $record->user;

        if (! $user && $createSystemUser) {
            if (blank($userPassword)) {
                throw ValidationException::withMessages([
                    'user_password' => 'Password is required to create a system account.',
                ]);
            }

            if (blank($userUsername)) {
                throw ValidationException::withMessages([
                    'user_username' => 'Username is required to create a system account.',
                ]);
            }

            $name = trim((string) (($record->first_name_en ?? '') . ' ' . ($record->last_name_en ?? '')));
            if ($name === '') {
                $name = trim((string) (($record->first_name_am ?? '') . ' ' . ($record->last_name_am ?? '')));
            }

            $user = User::create([
                'name' => $name !== '' ? $name : 'Paramilitary',
                'email' => $record->email,
                'username' => $userUsername,
                'password' => (string) $userPassword,
            ]);

            $record->update(['user_id' => $user->id]);
        }

        if ($user) {
            $updates = [
                'email' => $record->email,
                'name' => trim((string) (($record->first_name_en ?? '') . ' ' . ($record->last_name_en ?? ''))) ?: $user->name,
                'username' => $userUsername ?: $user->username,
            ];

            if (filled($userPassword)) {
                $updates['password'] = (string) $userPassword;
            }

            $user->update($updates);

            if (! empty($userRoles)) {
                $user->syncRoles($userRoles);
            }
        }

        return $record;
    }
}
