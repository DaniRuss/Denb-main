<?php

namespace App\Support;

use Carbon\Carbon;
use Throwable;

class EthiopianDate
{
    public const MONTHS_AM = [
        1 => 'መስከረም',
        2 => 'ጥቅምት',
        3 => 'ኅዳር',
        4 => 'ታኅሳስ',
        5 => 'ጥር',
        6 => 'የካቲት',
        7 => 'መጋቢት',
        8 => 'ሚያዝያ',
        9 => 'ግንቦት',
        10 => 'ሰኔ',
        11 => 'ሐምሌ',
        12 => 'ነሐሴ',
        13 => 'ጳጉሜን',
    ];

    public static function toEcYmd($gregorian): ?string
    {
        if (! $gregorian) {
            return null;
        }

        try {
            $carbon = ($gregorian instanceof Carbon ? $gregorian : Carbon::parse($gregorian))
                ->copy()
                ->timezone('Africa/Addis_Ababa')
                ->startOfDay();

            $newYear = self::ethiopianNewYearForGregorianYear((int) $carbon->format('Y'));
            if ($carbon->lt($newYear)) {
                $newYear = self::ethiopianNewYearForGregorianYear((int) $carbon->format('Y') - 1);
            }

            $dayOfYear = (int) $newYear->diffInDays($carbon) + 1;
            $month = intdiv($dayOfYear - 1, 30) + 1;
            $day = (($dayOfYear - 1) % 30) + 1;
            $year = (int) $newYear->format('Y') - 7;

            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        } catch (Throwable) {
            return null;
        }
    }

    public static function toEcYmdHi($gregorian): ?string
    {
        if (! $gregorian) {
            return null;
        }

        try {
            $carbon = $gregorian instanceof Carbon ? $gregorian : Carbon::parse($gregorian);
            $date = self::toEcYmd($carbon);
            if (! $date) {
                return null;
            }

            return $date.' '.$carbon->format('H:i');
        } catch (Throwable) {
            return null;
        }
    }

    public static function fromEcYmd(string $ecYmd, ?string $timezone = 'Africa/Addis_Ababa'): Carbon
    {
        [$y, $m, $d] = array_map('intval', explode('-', trim($ecYmd)));

        return self::ethiopianNewYearForGregorianYear($y + 7, $timezone ?: 'Africa/Addis_Ababa')
            ->addDays((($m - 1) * 30) + ($d - 1))
            ->startOfDay();
    }

    public static function splitEcYmd(?string $ecYmd): ?array
    {
        if (! $ecYmd) {
            return null;
        }

        $parts = explode('-', trim($ecYmd));
        if (count($parts) !== 3) {
            return null;
        }

        return [
            'y' => (int) $parts[0],
            'm' => (int) $parts[1],
            'd' => (int) $parts[2],
        ];
    }

    public static function daysInEcMonth(int $year, int $month): int
    {
        if ($month >= 1 && $month <= 12) {
            return 30;
        }

        // Pagume: 5 days, or 6 days on leap years.
        // We detect leap year by attempting to construct day 6.
        try {
            return (($year + 1) % 4) === 0 ? 6 : 5;
        } catch (Throwable) {
            return 5;
        }
    }

    protected static function ethiopianNewYearForGregorianYear(
        int $gregorianYear,
        string $timezone = 'Africa/Addis_Ababa'
    ): Carbon {
        $septemberDay = Carbon::create($gregorianYear + 1, 1, 1, 0, 0, 0, $timezone)->isLeapYear()
            ? 12
            : 11;

        return Carbon::create($gregorianYear, 9, $septemberDay, 0, 0, 0, $timezone)->startOfDay();
    }

    /**
     * Gregorian calendar date (Y-m-d) for “today” in Africa/Addis_Ababa.
     * Use for shift/attendance queries so the operational day matches Ethiopian local time.
     */
    public static function todayGregorianInAddisAbaba(): string
    {
        return Carbon::now('Africa/Addis_Ababa')->toDateString();
    }

    /**
     * Ethiopian date as "d monthName y" with Amharic month name (e.g. 21 መጋቢት 2017).
     */
    public static function toEcYmdAmharic($gregorian): ?string
    {
        $ec = self::toEcYmd($gregorian);
        if (! $ec) {
            return null;
        }

        $parts = self::splitEcYmd($ec);
        if (! $parts) {
            return null;
        }

        $monthName = self::MONTHS_AM[$parts['m']] ?? sprintf('%02d', $parts['m']);

        return sprintf('%d %s %d', $parts['d'], $monthName, $parts['y']);
    }

    /**
     * Ethiopian calendar date (Amharic month) + Ethiopian traditional clock (see EthiopianTime).
     */
    public static function toEcAmharicDateAndTime($gregorian): ?string
    {
        if (! $gregorian) {
            return null;
        }

        try {
            $carbon = $gregorian instanceof Carbon ? $gregorian : Carbon::parse($gregorian);
            $carbon = $carbon->copy()->timezone('Africa/Addis_Ababa');
            $datePart = self::toEcYmdAmharic($carbon);
            if (! $datePart) {
                return null;
            }

            return $datePart.' · '.EthiopianTime::formatInstantEthiopianClock($carbon);
        } catch (Throwable) {
            return null;
        }
    }
}
