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
        return $user->hasRole(['admin', 'supervisor', 'officer']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IllegalAsset $illegalAsset): bool
    {
        if ($user->hasRole('admin')) return true;
        
        // Supervisor can view assets in their department
        if ($user->hasRole('supervisor')) {
            return $user->department_id === $illegalAsset->department_id;
        }

        // Officer can view their own registered assets
        return $user->hasRole('officer') && $user->id === $illegalAsset->officer_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'supervisor', 'officer']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IllegalAsset $illegalAsset): bool
    {
        if ($user->hasRole('admin')) return true;
        
        // Supervisor can update assets in their department
        if ($user->hasRole('supervisor')) {
            return $user->department_id === $illegalAsset->department_id;
        }

        // Officer can only update if it is still 'Registered' and they are the owner
        if ($user->hasRole('officer') && $illegalAsset->status === 'Registered') {
            return $user->id === $illegalAsset->officer_id;
        }

        return false;
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
        return $user->hasRole('admin') || 
               ($user->hasRole('supervisor') && $user->department_id === $illegalAsset->department_id);
    }

    public function estimate(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('supervisor') && $user->department_id === $illegalAsset->department_id);
    }

    public function transfer(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole('admin');
    }

    public function sell(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole('admin');
    }

    public function dispose(User $user, IllegalAsset $illegalAsset): bool
    {
        return $user->hasRole('admin');
    }
}
