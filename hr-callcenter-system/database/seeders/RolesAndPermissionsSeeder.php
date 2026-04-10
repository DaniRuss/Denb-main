<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Existing
            'manage_users',
            'manage_roles',
            'view_complaints',
            'manage_complaints',
            'assign_cases',
            'report_tips',
            'create_call_tips',
            'view_own_call_tips',
            'review_supervisor_call_tips',
            'review_director_call_tips',
            'manage_sub_city_call_tips',
            'manage_woreda_call_tips',
            'manage_call_tip_workflow',
            'manage_penalty_action',
            'view_reports',
            'manage_inventory',

            // Penalty & Action – granular
            'create_violation_records',       // officer: record violations on the street
            'view_violation_records',         // view violation records
            'edit_violation_records',         // edit/update violation records
            'issue_penalty_receipts',         // officer: issue penalty receipts to violators
            'issue_warning_letters',          // officer: issue 3-day and 24-hour warnings
            'seize_assets',                   // officer: confiscate assets from violators
            'verify_violations',              // supervisor: verify/approve violation records
            'manage_violators',               // register and manage violator records
            'manage_penalty_schedules',       // admin: configure tariff levels and violation types
            'manage_confiscated_assets',      // manage full asset lifecycle (handover, estimate, transfer, sell, dispose)
            'track_payments',                 // track penalty payment status, escalate to court
            'escalate_to_court',              // file court cases for non-payment
            'escalate_to_task_force',         // escalate warning letters to task force for demolition
            'view_sub_city_violations',       // sub-city officer: view violations in their sub-city

            // Shift Management
            'view_shifts',
            'manage_shifts',
            'assign_shifts',
            'view_attendance',
            'manage_attendance',
            'verify_attendance',
            'override_attendance_lock',
            'approve_shift_swap',
            'submit_shift_report',
            'view_shift_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create Roles and assign permissions
        $roleAdmin = Role::findOrCreate('admin');
        $roleAdmin->givePermissionTo(Permission::all());

        $roleSupervisor = Role::findOrCreate('supervisor');
        $roleSupervisor->givePermissionTo([
            'view_complaints',
            'manage_complaints',
            'assign_cases',
            'view_reports',
            'review_supervisor_call_tips',
            // Shift management – supervisors oversee but don't override locks.
            'view_shifts',
            'assign_shifts',
            'view_attendance',
            'verify_attendance',
            'approve_shift_swap',
            'view_shift_reports',
            // Penalty & Action – supervisors verify, escalate, and manage assets.
            'view_violation_records',
            'edit_violation_records',
            'verify_violations',
            'manage_violators',
            'manage_confiscated_assets',
            'track_payments',
            'escalate_to_court',
            'escalate_to_task_force',
        ]);

        $roleOfficer = Role::findOrCreate('officer');
        $roleOfficer->givePermissionTo([
            'view_complaints',
            'manage_complaints',
            // Shift management – front-line officers record attendance & reports.
            'view_shifts',
            'manage_attendance',
            'submit_shift_report',
            // Penalty & Action – officers detect violations and take action on the street.
            'create_violation_records',
            'view_violation_records',
            'issue_penalty_receipts',
            'issue_warning_letters',
            'seize_assets',
            'manage_violators',
        ]);

        $callRecordOfficer = Role::findOrCreate('call_record_officer');
        $callRecordOfficer->givePermissionTo(['create_call_tips', 'view_own_call_tips']);

        $callCenterDirector = Role::findOrCreate('call_center_director');
        $callCenterDirector->givePermissionTo(['review_director_call_tips']);

        $subCityOfficer = Role::findOrCreate('sub_city_officer');
        $subCityOfficer->givePermissionTo(['manage_sub_city_call_tips']);

        $woredaOfficer = Role::findOrCreate('woreda_officer');
        $woredaOfficer->givePermissionTo(['manage_woreda_call_tips']);

        $penaltyActionOfficer = Role::findOrCreate('penalty_action_officer');
        $penaltyActionOfficer->givePermissionTo([
            'manage_penalty_action',
            'create_violation_records',
            'view_violation_records',
            'edit_violation_records',
            'issue_penalty_receipts',
            'issue_warning_letters',
            'seize_assets',
            'manage_violators',
            'manage_confiscated_assets',
            'track_payments',
            'escalate_to_court',
            'escalate_to_task_force',
        ]);

        $subCityOfficer = Role::findOrCreate('sub_city_officer');
        // sub_city_officer already exists — add penalty permissions scoped to sub-city
        $subCityOfficer->givePermissionTo(array_merge(
            $subCityOfficer->permissions->pluck('name')->toArray(),
            [
                'view_violation_records',
                'view_sub_city_violations',
                'manage_confiscated_assets',
                'track_payments',
            ]
        ));

        // Create Super Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@aalea.gov.et'],
            [
                'name' => 'Super Admin',
                'username' => 'Super Admin',
                'password' => Hash::make('admin123'),
            ]
        );
        $admin->assignRole($roleAdmin);

        echo "RBAC Seeded successfully! Login: admin@aalea.gov.et / admin123\n";
    }
}