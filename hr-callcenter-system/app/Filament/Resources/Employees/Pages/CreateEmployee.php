<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Filament\Support\Enums\Width;
use Illuminate\Validation\ValidationException;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    public function getMaxContentWidth(): \Filament\Support\Enums\Width|string|null
    {
        return Width::FiveExtraLarge;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $createSystemUser = (bool) ($data['create_system_user'] ?? true);
        $userPassword = $data['user_password'] ?? null;
        $userRoles = $data['user_roles'] ?? [];
        $userUsername = $data['user_username'] ?? ($data['email'] ?? null);

        unset($data['create_system_user'], $data['user_password'], $data['user_roles'], $data['user_username']);

        if ($createSystemUser) {
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

            $name = trim((string) (($data['first_name_en'] ?? '') . ' ' . ($data['last_name_en'] ?? '')));
            if ($name === '') {
                $name = trim((string) (($data['first_name_am'] ?? '') . ' ' . ($data['last_name_am'] ?? '')));
            }

            $user = User::create([
                'name' => $name !== '' ? $name : 'Employee',
                'email' => $data['email'],
                'username' => $userUsername,
                'password' => (string) $userPassword,
            ]);

            if (! empty($userRoles)) {
                $user->syncRoles($userRoles);
            }

            $data['user_id'] = $user->id;
        }

        return static::getModel()::create($data);
    }
}
