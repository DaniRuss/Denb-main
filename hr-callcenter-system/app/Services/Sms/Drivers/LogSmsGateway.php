<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\Contracts\SmsGateway;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogSmsGateway implements SmsGateway
{
    public function __construct(private readonly string $channel = 'stack')
    {
    }

    public function send(string $to, string $message, array $options = []): SmsResult
    {
        $id = 'log-' . Str::uuid()->toString();

        Log::channel($this->channel)->info('[SMS] ' . $to . ' :: ' . $message, [
            'provider_message_id' => $id,
            'options' => $options,
        ]);

        return SmsResult::ok($id, ['channel' => $this->channel]);
    }

    public function name(): string
    {
        return 'log';
    }
}
