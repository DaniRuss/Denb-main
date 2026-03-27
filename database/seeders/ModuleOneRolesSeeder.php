<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

/**
 * Module One Role Seeder
 *
 * Creates all roles and demo users for the Information & Awareness Management
 * module as defined in module_one_projectplan.md and plan_step.md.
 *
 * Role Hierarchy (top → bottom):
 *   super_admin → admin → woreda_coordinator → paramilitary (field) → officer
 */
class ModuleOneRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles/permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── 1. Define all permissions ─────────────────────────────────
        $allPermissions = [
            // System
            'manage_users',
            'manage_roles',
            'manage_settings',
            // Campaigns
            'create_campaigns',
            'view_campaigns',
            'edit_campaigns',
            'delete_campaigns',
            // Engagement logs
            'create_engagements',
            'view_engagements',
            'edit_engagements',
            'approve_engagements',
            'reject_engagements',
            // Volunteer tips (Tikoma)
            'submit_tips',
            'view_tips',
            'verify_tips',
            'take_action_on_tips',
            // Analytics / Reports
            'view_reports',
            'view_all_reports',
            // Complaints
            'view_complaints',
            'manage_complaints',
            'assign_cases',
        ];

        foreach ($allPermissions as $perm) {
            Permission::findOrCreate($perm);
        }

        // ─── 2. Create Roles with appropriate permissions ──────────────

        // Super Admin — full access
        $superAdmin = Role::findOrCreate('super_admin');
        $superAdmin->syncPermissions(Permission::all());

        // Admin — city-wide management + analytics
        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions([
            'manage_users', 'manage_settings',
            'create_campaigns', 'view_campaigns', 'edit_campaigns', 'delete_campaigns',
            'view_engagements', 'approve_engagements', 'reject_engagements',
            'view_tips', 'verify_tips', 'take_action_on_tips',
            'view_reports', 'view_all_reports',
            'view_complaints', 'manage_complaints', 'assign_cases',
        ]);

        // Woreda Coordinator — approver for their woreda
        $woredaCoordinator = Role::findOrCreate('woreda_coordinator');
        $woredaCoordinator->syncPermissions([
            'view_campaigns',
            'create_engagements', 'view_engagements', 'edit_engagements',
            'approve_engagements', 'reject_engagements',
            'view_tips', 'verify_tips',
            'view_reports',
        ]);

        // Paramilitary (Field Officer) — creates engagements and submits tips
        $paramilitary = Role::findOrCreate('paramilitary');
        $paramilitary->syncPermissions([
            'view_campaigns',
            'create_engagements', 'view_engagements', 'edit_engagements',
            'submit_tips', 'view_tips',
        ]);

        // Officer — handles enforcement after tips are verified
        $officer = Role::findOrCreate('officer');
        $officer->syncPermissions([
            'view_campaigns',
            'view_engagements',
            'view_tips', 'take_action_on_tips',
            'view_complaints', 'manage_complaints',
            'view_reports',
        ]);

        // Keep legacy roles compatible
        Role::findOrCreate('supervisor')->syncPermissions([
            'view_complaints', 'manage_complaints', 'assign_cases', 'view_reports'
        ]);

        // ─── 3. Create Demo Users for each role ────────────────────────

        $users = [
            [
                'name'     => 'Super Admin',
                'email'    => 'superadmin@aalea.gov.et',
                'password' => 'Super@1234',
                'role'     => 'super_admin',
            ],
            [
                'name'     => 'City Admin',
                'email'    => 'admin@aalea.gov.et',
                'password' => 'Admin@1234',
                'role'     => 'admin',
            ],
            [
                'name'     => 'Woreda Coordinator',
                'email'    => 'coordinator@aalea.gov.et',
                'password' => 'Coord@1234',
                'role'     => 'woreda_coordinator',
                'sub_city_id' => 4,
                'woreda_id'   => 36,
            ],
            [
                'name'     => 'Field Officer (Paramilitary)',
                'email'    => 'field@aalea.gov.et',
                'password' => 'Field@1234',
                'role'     => 'paramilitary',
                'sub_city_id' => 4,
                'woreda_id'   => 36,
            ],
            [
                'name'     => 'Enforcement Officer',
                'email'    => 'officer@aalea.gov.et',
                'password' => 'Officer@1234',
                'role'     => 'officer',
                'sub_city_id' => 4,
                'woreda_id'   => 36,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name'     => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'sub_city_id' => $userData['sub_city_id'] ?? null,
                    'woreda_id'   => $userData['woreda_id'] ?? null,
                ]
            );
            // Sync — not assign — to avoid duplicate role assignments on re-seed
            $user->syncRoles([$userData['role']]);
        }

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command->info('║         Module One — Role-Based Demo Accounts Created        ║');
        $this->command->info('╠══════════════════════════════════════════════════════════════╣');
        $this->command->info('║  Role                  │ Email                     │ Password ║');
        $this->command->info('╠══════════════════════════════════════════════════════════════╣');
        $this->command->info('║  super_admin           │ superadmin@aalea.gov.et   │ Super@1234 ║');
        $this->command->info('║  admin                 │ admin@aalea.gov.et        │ Admin@1234 ║');
        $this->command->info('║  woreda_coordinator    │ coordinator@aalea.gov.et  │ Coord@1234 ║');
        $this->command->info('║  paramilitary (field)  │ field@aalea.gov.et        │ Field@1234 ║');
        $this->command->info('║  officer               │ officer@aalea.gov.et      │ Officer@1234 ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════╝');
        $this->command->info('  Login at: /admin');
        $this->command->info('');
    }
}
