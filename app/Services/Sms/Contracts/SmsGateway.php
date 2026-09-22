<?php

namespace App\Services\Sms\Contracts;

use App\Services\Sms\SmsResult;

interface SmsGateway
{
    public function send(string $to, string $message, array $options = []): SmsResult;

    public function name(): string;
}
