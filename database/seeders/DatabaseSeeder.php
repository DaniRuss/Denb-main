<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Make sure roles & permissions exist.
        $this->call(RolesAndPermissionsSeeder::class);

        // Create test users for main roles.
        $adminUser = User::updateOrCreate(
            ['email' => 'admin-test@example.com'],
            [
                'name' => 'Admin Test',
                'username' => 'admin_test',
                'password' => bcrypt('password'),
            ]
        );
        $adminUser->syncRoles(['admin']);

        $supervisorUser = User::updateOrCreate(
            ['email' => 'supervisor-test@example.com'],
            [
                'name' => 'Supervisor Test',
                'username' => 'supervisor_test',
                'password' => bcrypt('password'),
            ]
        );
        $supervisorUser->syncRoles(['supervisor']);

        $officerUser = User::updateOrCreate(
            ['email' => 'officer-test@example.com'],
            [
                'name' => 'Officer Test',
                'username' => 'officer_test',
                'password' => bcrypt('password'),
            ]
        );
        $officerUser->syncRoles(['officer']);

        // Seed shift types (Morning, Afternoon, Night). Run after migrations.
        $this->call(ShiftsSeeder::class);
    }
}