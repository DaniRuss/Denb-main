<?php

namespace App\Support;

use Andegna\DateTimeFactory;
use Carbon\Carbon;
use DateTimeZone;
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
            $carbon = $gregorian instanceof Carbon ? $gregorian : Carbon::parse($gregorian);
            $ethiopic = DateTimeFactory::fromDateTime($carbon->toDateTime());

            return sprintf('%04d-%02d-%02d', $ethiopic->getYear(), $ethiopic->getMonth(), $ethiopic->getDay());
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

            return $date . ' ' . $carbon->format('H:i');
        } catch (Throwable) {
            return null;
        }
    }

    public static function fromEcYmd(string $ecYmd, ?string $timezone = 'Africa/Addis_Ababa'): Carbon
    {
        [$y, $m, $d] = array_map('intval', explode('-', trim($ecYmd)));

        $tz = new DateTimeZone($timezone ?: 'Africa/Addis_Ababa');
        $ethiopic = DateTimeFactory::of($y, $m, $d, 0, 0, 0, $tz);

        return Carbon::instance($ethiopic->toGregorian())->startOfDay();
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
            DateTimeFactory::of($year, 13, 6);
            return 6;
        } catch (Throwable) {
            return 5;
        }
    }
}

