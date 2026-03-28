<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Support\EthiopianTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ShiftsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('shifts')) {
            return;
        }
        // Ethiopian clock only (12-hour, two cycles). See App\Support\EthiopianTime.
        // Morning 1:00–7:00, afternoon 7:00–12:45, night 12:45–4:00 (evening cycle for end).
        $shifts = [
            [
                'name' => 'Morning',
                'start_eth' => '01:00',
                'start_cycle' => EthiopianTime::CYCLE_DAY,
                'end_eth' => '07:00',
                'end_cycle' => EthiopianTime::CYCLE_DAY,
                'description' => 'Morning shift (Ethiopian 1:00 – 7:00, day)',
                'is_active' => true,
            ],
            [
                'name' => 'Afternoon',
                'start_eth' => '07:00',
                'start_cycle' => EthiopianTime::CYCLE_DAY,
                'end_eth' => '12:45',
                'end_cycle' => EthiopianTime::CYCLE_DAY,
                'description' => 'Afternoon shift (Ethiopian 7:00 – 12:45, day)',
                'is_active' => true,
            ],
            [
                'name' => 'Night',
                'start_eth' => '12:45',
                'start_cycle' => EthiopianTime::CYCLE_DAY,
                'end_eth' => '04:00',
                'end_cycle' => EthiopianTime::CYCLE_EVENING,
                'description' => 'Night shift (Ethiopian 12:45 – 4:00, day → evening)',
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
