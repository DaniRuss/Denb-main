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
        return true; // Or restrict if necessary
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IllegalAsset $illegalAsset): bool
    {
        if ($this->isAdmin($user)) return true;
        
        // Supervisor can view assets in their department
        if ($this->isSupervisor($user)) {
            return $user->department_id === $illegalAsset->department_id;
        }

        // Officer can view their own registered assets
        return $user->id === $illegalAsset->officer_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin, Supervisor, and Officer can register assets
        return $this->isAdmin($user) || $this->isSupervisor($user) || $this->isOfficer($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IllegalAsset $illegalAsset): bool
    {
        if ($this->isAdmin($user)) return true;
        
        // Supervisor can update assets in their department
        if ($this->isSupervisor($user)) {
            return $user->department_id === $illegalAsset->department_id;
        }

        // Officer can only update if it is still 'Registered'
        if ($this->isOfficer($user) && $illegalAsset->status === 'Registered') {
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
        return $this->isAdmin($user);
    }

    // Helper methods for roles (adjust according to your exact role architecture)
    private function isAdmin(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('Admin') || (isset($user->role) && $user->role === 'Admin');
    }

    private function isSupervisor(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('Supervisor') || (isset($user->role) && $user->role === 'Supervisor');
    }

    private function isOfficer(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('Officer') || (isset($user->role) && $user->role === 'Officer');
    }
}
