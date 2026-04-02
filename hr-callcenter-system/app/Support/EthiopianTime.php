<?php

namespace App\Support;

use App\Models\Shift;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Ethiopian traditional clock: two 12-hour cycles from local convention used in Ethiopia.
 *
 * - Cycle "day" (ቀን): Ethiopian 1:00 = 07:00 through Ethiopian 12:00 = 18:00 (local).
 * - Cycle "evening" (ምሽት): Ethiopian 1:00 = 19:00 through Ethiopian 12:00 = 06:00 next morning.
 *
 * All shift definitions use this clock only; comparisons with "now" convert to Africa/Addis_Ababa instants.
 */
class EthiopianTime
{
    /** 07:00–18:59 local — Ethiopian hours 1–12 (day). */
    public const CYCLE_DAY = 0;

    /** 19:00–06:59 local — Ethiopian hours 1–12 (evening through dawn). */
    public const CYCLE_EVENING = 1;

    /**
     * @return array{0: string, 1: int} [normalized 'H:i', cycle]
     */
    public static function fromGregorianTimeString(string $hms): array
    {
        $c = Carbon::parse('2000-01-01 '.$hms, 'Africa/Addis_Ababa');
        $h24 = (int) $c->format('G');
        $m = (int) $c->format('i');

        return self::from24Hour($h24, $m);
    }

    /**
     * @return array{0: string, 1: int} [normalized 'H:i', cycle]
     */
    public static function from24Hour(int $h24, int $m): array
    {
        $m = max(0, min(59, $m));

        if ($h24 >= 7 && $h24 <= 18) {
            $cycle = self::CYCLE_DAY;
            $ethH = $h24 - 6;
        } elseif ($h24 >= 19) {
            $cycle = self::CYCLE_EVENING;
            $ethH = $h24 - 18;
        } else {
            $cycle = self::CYCLE_EVENING;
            $ethH = $h24 + 6;
        }

        return [self::normalizeEthHm(sprintf('%d:%02d', $ethH, $m)), $cycle];
    }

    public static function normalizeEthHm(string $value): string
    {
        $value = trim($value);
        $parts = explode(':', $value);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException(__('Invalid Ethiopian time; use hour 1–12 and minutes, e.g. 1:45.'));
        }

        $h = (int) $parts[0];
        $m = (int) $parts[1];

        if ($h < 1 || $h > 12 || $m < 0 || $m > 59) {
            throw new InvalidArgumentException(__('Invalid Ethiopian time; use hour 1–12 and minutes, e.g. 1:45.'));
        }

        return sprintf('%02d:%02d', $h, $m);
    }

    /**
     * @return array{hour: int, minute: int}
     */
    public static function parseEthHm(string $ethHm): array
    {
        $ethHm = self::normalizeEthHm($ethHm);
        [$h, $m] = array_map('intval', explode(':', $ethHm));

        return ['hour' => $h, 'minute' => $m];
    }

    /**
     * @return array{0: int, 1: int} [hour24, minute]
     */
    public static function ethTo24Hour(int $ethH, int $ethM, int $cycle): array
    {
        if ($ethH < 1 || $ethH > 12 || $ethM < 0 || $ethM > 59) {
            throw new InvalidArgumentException(__('Ethiopian hour must be 1–12.'));
        }

        if ($cycle === self::CYCLE_DAY) {
            $h24 = 6 + $ethH;
            if ($ethH === 12) {
                $h24 = 18;
            }
        } else {
            $h24 = 18 + $ethH;
            if ($h24 >= 24) {
                $h24 -= 24;
            }
        }

        return [$h24, $ethM];
    }

    public static function toCarbonOnLocalDate(
        Carbon $localDayStart,
        string $ethHm,
        int $cycle,
        string $tz = 'Africa/Addis_Ababa'
    ): Carbon {
        $localDayStart = $localDayStart->copy()->timezone($tz)->startOfDay();
        $parts = self::parseEthHm($ethHm);
        [$h24, $m] = self::ethTo24Hour($parts['hour'], $parts['minute'], $cycle);

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $localDayStart->format('Y-m-d').sprintf(' %02d:%02d:00', $h24, $m),
            $tz
        );
    }

    /**
     * Shift window in local timezone for a given assignment calendar day (start of day).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function shiftWindowOnLocalDate(Shift $shift, Carbon $localAssignmentDay): array
    {
        $tz = 'Africa/Addis_Ababa';
        $localAssignmentDay = $localAssignmentDay->copy()->timezone($tz)->startOfDay();

        $start = self::toCarbonOnLocalDate($localAssignmentDay, $shift->start_eth, (int) $shift->start_cycle, $tz);
        $end = self::toCarbonOnLocalDate($localAssignmentDay, $shift->end_eth, (int) $shift->end_cycle, $tz);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }

    /**
     * Minutes since local midnight for sorting (0–1439).
     */
    public static function minutesSinceMidnight(string $ethHm, int $cycle): int
    {
        $parts = self::parseEthHm($ethHm);
        [$h24, $m] = self::ethTo24Hour($parts['hour'], $parts['minute'], $cycle);

        return $h24 * 60 + $m;
    }

    public static function sortKey(Shift $shift): int
    {
        return self::minutesSinceMidnight($shift->start_eth, (int) $shift->start_cycle);
    }

    public static function cycleLabel(int $cycle): string
    {
        return $cycle === self::CYCLE_DAY
            ? __('Day (1–12 from 7:00)')
            : __('Evening–dawn (1–12 from 7:00)');
    }

    public static function formatEthAndCycle(?string $ethHm, ?int $cycle): string
    {
        if ($ethHm === null || $ethHm === '' || $cycle === null) {
            return '—';
        }

        return self::normalizeEthHm($ethHm).' · '.self::cycleLabel((int) $cycle);
    }

    /**
     * Format an instant in Africa/Addis_Ababa as Ethiopian clock + cycle label.
     */
    public static function formatInstantEthiopianClock(Carbon $at): string
    {
        $at = $at->copy()->timezone('Africa/Addis_Ababa');
        $h24 = (int) $at->format('G');
        $m = (int) $at->format('i');
        [$ethHm, $cycle] = self::from24Hour($h24, $m);

        return $ethHm.' · '.self::cycleLabel($cycle);
    }
}
