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
            'manage_call_tip_workflow',
            'manage_penalty_action',
            'view_reports',
            'manage_inventory',
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
        ]);

        $roleOfficer = Role::findOrCreate('officer');
        $roleOfficer->givePermissionTo(['view_complaints', 'manage_complaints']);

        $callRecordOfficer = Role::findOrCreate('call_record_officer');
        $callRecordOfficer->givePermissionTo(['create_call_tips', 'view_own_call_tips']);

        $callCenterDirector = Role::findOrCreate('call_center_director');
        $callCenterDirector->givePermissionTo(['review_director_call_tips']);

        $subCityOfficer = Role::findOrCreate('sub_city_officer');
        $subCityOfficer->givePermissionTo(['manage_sub_city_call_tips']);

        $penaltyActionOfficer = Role::findOrCreate('penalty_action_officer');
        $penaltyActionOfficer->givePermissionTo(['manage_penalty_action']);

        // Create Super Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@aalea.gov.et'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
            ]
        );
        $admin->assignRole($roleAdmin);

        echo "RBAC Seeded successfully! Login: admin@aalea.gov.et / admin123\n";
    }
}
