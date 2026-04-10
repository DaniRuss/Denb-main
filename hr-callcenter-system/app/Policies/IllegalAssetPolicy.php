<?php

namespace App\Policies;

use App\Models\IllegalAsset;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IllegalAssetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'officer', 'woreda_officer', 'sub_city_officer', 'supervisor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole(['admin', 'officer', 'woreda_officer', 'sub_city_officer', 'supervisor']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'officer', 'supervisor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole(['admin', 'officer', 'woreda_officer', 'sub_city_officer', 'supervisor']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IllegalAsset $illegalAsset): bool
    {
        // Only Admins can delete assets completely
        return $user->hasRole('admin');
    }

    // Helper methods for specific asset actions (can be called via Gate::allows)
    
    public function handover(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole(['admin', 'officer', 'woreda_officer']);
    }

    public function estimate(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole(['admin', 'officer']);
    }

    public function transfer(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole('admin');
    }

    public function sell(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole(['admin', 'woreda_officer', 'sub_city_officer']);
    }

    public function dispose(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole(['admin', 'woreda_officer', 'sub_city_officer']);
    }
}
