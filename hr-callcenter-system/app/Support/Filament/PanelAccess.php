<?php

namespace App\Support\Filament;

use App\Models\User;

class PanelAccess
{
    /** @var array<int, array<string, bool>> */
    protected static array $roleChecks = [];

    /** @var array<int, array<string, bool>> */
    protected static array $permissionChecks = [];

    public static function user(): ?User
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user;
    }

    public static function hasRole(string $role): bool
    {
        $user = static::user();

        if (! $user) {
            return false;
        }

        static::loadAccessRelations($user);

        return static::$roleChecks[$user->id][$role] ??= $user->hasRole($role);
    }

    public static function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if (static::hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public static function can(string $permission): bool
    {
        $user = static::user();

        if (! $user) {
            return false;
        }

        static::loadAccessRelations($user);

        return static::$permissionChecks[$user->id][$permission] ??= $user->can($permission);
    }

    public static function allows(array $permissions = [], array $roles = [], bool $allowAdmin = true): bool
    {
        $user = static::user();

        if (! $user) {
            return false;
        }

        if ($allowAdmin && static::hasRole('admin')) {
            return true;
        }

        if ($roles !== [] && static::hasAnyRole($roles)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (static::can($permission)) {
                return true;
            }
        }

        return false;
    }

    public static function isAdmin(): bool
    {
        return static::allows();
    }

    protected static function loadAccessRelations(User $user): void
    {
        $user->loadMissing('roles', 'permissions', 'roles.permissions');
    }
}
