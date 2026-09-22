<?php

namespace App\Services\Sms;

class SmsResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $providerMessageId = null,
        public readonly ?string $error = null,
        public readonly array $raw = [],
    ) {
    }

    public static function ok(?string $providerMessageId = null, array $raw = []): self
    {
        return new self(true, $providerMessageId, null, $raw);
    }

    public static function fail(string $error, array $raw = []): self
    {
        return new self(false, null, $error, $raw);
    }
}
