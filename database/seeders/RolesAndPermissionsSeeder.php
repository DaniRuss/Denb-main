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
        $roleSupervisor->givePermissionTo(['view_complaints', 'manage_complaints', 'assign_cases', 'view_reports']);

        $roleOfficer = Role::findOrCreate('officer');
        $roleOfficer->givePermissionTo(['view_complaints', 'manage_complaints']);

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
