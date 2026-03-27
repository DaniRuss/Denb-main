<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * StrictSeparationOfDutySeeder
 *
 * Purpose: Enforce government-grade separation of duty.
 * Admin and Super Admin MUST NOT have the ability to approve, reject,
 * or verify field-level operational records. Those actions belong
 * exclusively to the Woreda Coordinator role.
 *
 * Run: php artisan db:seed --class=StrictSeparationOfDutySeeder
 */
class StrictSeparationOfDutySeeder extends Seeder
{
    /**
     * The permissions that are EXCLUSIVELY for Woreda Coordinators.
     * Admins and Super Admins oversee the system — they do NOT
     * participate in the operational approval chain.
     */
    private const COORDINATOR_ONLY_PERMISSIONS = [
        'approve_engagements',
        'reject_engagements',
        'verify_tips',
    ];

    public function run(): void
    {
        $roles = ['super_admin', 'admin'];

        foreach ($roles as $roleName) {
            $role = Role::findByName($roleName, 'web');

            if ($role) {
                $role->revokePermissionTo(self::COORDINATOR_ONLY_PERMISSIONS);
                $this->command->info("✓ Revoked [approve_engagements, reject_engagements, verify_tips] from role: {$roleName}");
            } else {
                $this->command->warn("⚠ Role '{$roleName}' not found. Skipping.");
            }
        }

        $this->command->info('');
        $this->command->info('✅ Separation of duty enforced successfully.');
        $this->command->info('   Approve, Reject, and Verify actions are now exclusive to: woreda_coordinator');
    }
}
