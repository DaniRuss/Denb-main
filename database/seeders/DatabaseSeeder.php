<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters:
     *  1. Locations (sub_cities, woredas) must exist before users reference them
     *  2. Roles & Permissions + Demo Users created next
     *  3. Site Settings last (independent)
     */
    public function run(): void
    {
        $this->call([
            AddisAbabaLocationSeeder::class,   // Sub-cities & Woredas
            RolesAndPermissionsSeeder::class,  // Base Spatie roles
            ModuleOneRolesSeeder::class,       // Module One roles, permissions & demo users
            SiteSettingSeeder::class,          // Default site settings
        ]);
    }
}

