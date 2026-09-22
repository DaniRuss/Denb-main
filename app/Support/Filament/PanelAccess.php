<?php

namespace App\Support\Filament;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PanelAccess
{
    /**
     * Get the currently authenticated user.
     */
    public static function user(): ?User
    {
        return Auth::user();
    }

    /**
     * Check if the authenticated user has the admin role.
     */
    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && $user->hasRole('admin');
    }

    /**
     * Check if the authenticated user has a specific role.
     */
    public static function hasRole(string $role): bool
    {
        $user = self::user();
        return $user && $user->hasRole($role);
    }

    /**
     * Check if the authenticated user has any of the given permissions.
     */
    public static function allows(array|string $permissions): bool
    {
        $user = self::user();

        if (!$user) {
            return false;
        }

        // Often admins are allowed everything
        if (self::isAdmin()) {
            return true;
        }

        if (is_array($permissions)) {
            return $user->hasAnyPermission($permissions);
        }

        return $user->hasPermissionTo($permissions);
    }
}
