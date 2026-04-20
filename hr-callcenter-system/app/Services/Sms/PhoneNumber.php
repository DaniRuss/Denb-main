<?php

namespace App\Services\Sms;

class PhoneNumber
{
    public static function normalize(?string $raw, string $country = '251'): ?string
    {
        if ($raw === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '' || $digits === null) {
            return null;
        }

        if (str_starts_with($digits, $country)) {
            $local = substr($digits, strlen($country));
            return self::isValidLocal($local) ? $country . $local : null;
        }

        if (str_starts_with($digits, '0')) {
            $local = substr($digits, 1);
            return self::isValidLocal($local) ? $country . $local : null;
        }

        return self::isValidLocal($digits) ? $country . $digits : null;
    }

    private static function isValidLocal(string $local): bool
    {
        return strlen($local) === 9 && ctype_digit($local);
    }
}
