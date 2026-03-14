<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ShiftsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('shifts')) {
            return;
        }
        $shifts = [
            [
                'name' => 'Morning',
                'start_time' => '01:45',
                'end_time' => '07:00',
                'description' => 'Morning shift (1:45 – 7:00 local time)',
                'is_active' => true,
            ],
            [
                'name' => 'Afternoon',
                'start_time' => '07:00',
                'end_time' => '12:45',
                'description' => 'Afternoon shift (7:00 – 12:45 local time)',
                'is_active' => true,
            ],
            [
                'name' => 'Night',
                'start_time' => '12:45',
                'end_time' => '04:00',
                'description' => 'Night shift (12:45 – 4:00 local time)',
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $data) {
            Shift::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        echo "Shifts seeded successfully (Morning, Afternoon, Night).\n";
    }
}
