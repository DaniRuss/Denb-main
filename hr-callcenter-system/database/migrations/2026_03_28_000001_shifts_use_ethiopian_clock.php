<?php

use App\Support\EthiopianTime;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shifts')) {
            return;
        }

        if (! Schema::hasColumn('shifts', 'start_eth')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->string('start_eth', 5)->nullable();
                $table->unsignedTinyInteger('start_cycle')->nullable();
                $table->string('end_eth', 5)->nullable();
                $table->unsignedTinyInteger('end_cycle')->nullable();
            });
        }

        $rows = DB::table('shifts')->orderBy('id')->get();

        foreach ($rows as $row) {
            if (! isset($row->start_time, $row->end_time)) {
                continue;
            }

            $start = (string) $row->start_time;
            $end = (string) $row->end_time;

            [$startEth, $startCycle] = EthiopianTime::fromGregorianTimeString($start);
            [$endEth, $endCycle] = EthiopianTime::fromGregorianTimeString($end);

            DB::table('shifts')->where('id', $row->id)->update([
                'start_eth' => $startEth,
                'start_cycle' => $startCycle,
                'end_eth' => $endEth,
                'end_cycle' => $endCycle,
            ]);
        }

        if (Schema::hasColumn('shifts', 'start_time')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->dropColumn(['start_time', 'end_time']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('shifts')) {
            return;
        }

        if (! Schema::hasColumn('shifts', 'start_eth')) {
            return;
        }

        Schema::table('shifts', function (Blueprint $table) {
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });

        $rows = DB::table('shifts')->orderBy('id')->get();

        foreach ($rows as $row) {
            $partsS = EthiopianTime::parseEthHm($row->start_eth);
            $partsE = EthiopianTime::parseEthHm($row->end_eth);
            [$h24S, $mS] = EthiopianTime::ethTo24Hour($partsS['hour'], $partsS['minute'], (int) $row->start_cycle);
            [$h24E, $mE] = EthiopianTime::ethTo24Hour($partsE['hour'], $partsE['minute'], (int) $row->end_cycle);

            DB::table('shifts')->where('id', $row->id)->update([
                'start_time' => sprintf('%02d:%02d:00', $h24S, $mS),
                'end_time' => sprintf('%02d:%02d:00', $h24E, $mE),
            ]);
        }

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['start_eth', 'start_cycle', 'end_eth', 'end_cycle']);
        });
    }
};
