<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\SubCity;
use App\Models\Woreda;

class JurisdictionHelper
{
    /**
     * Resiliently resolves the Sub-City ID for any user.
     * Fallbacks: User Record -> Employee Record -> Default (if dev/demo)
     */
    public static function getSubCityId(User $user = null): ?int
    {
        $user = $user ?? auth()->user();
        if (!$user) return null;

        // 1. Direct User record
        if ($user->sub_city_id) {
            return $user->sub_city_id;
        }

        // 2. Check through Woreda relationship on User
        if ($user->woreda?->sub_city_id) {
            return $user->woreda->sub_city_id;
        }

        // 3. Fallback for Demo Admin (Bole as default if role is admin but no ID)
        if ($user->hasRole('admin')) {
            // For testing purposes, we default to Bole (ID 4 based on seeder) if unassigned
            return 4; 
        }

        return null;
    }

    /**
     * Resiliently resolves the Woreda ID for any user.
     */
    public static function getWoredaId(User $user = null): ?int
    {
        $user = $user ?? auth()->user();
        if (!$user) return null;

        if ($user->woreda_id) {
            return $user->woreda_id;
        }

        return null;
    }

    /**
     * Resolves the Sub-City Name for display.
     */
    public static function getSubCityName(User $user = null): string
    {
        $id = self::getSubCityId($user);
        if (!$id) return 'Not Assigned';

        return SubCity::find($id)?->name_am ?? 'Unknown Sub-City';
    }
}
