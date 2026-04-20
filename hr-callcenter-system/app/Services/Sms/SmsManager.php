<?php

namespace App\Services\Sms;

use App\Models\SmsMessage;
use App\Services\Sms\Contracts\SmsGateway;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SmsManager
{
    /** @var array<string, SmsGateway> */
    protected array $resolved = [];

    public function __construct(
        protected Container $container,
        protected array $config,
    ) {
    }

    public function send(
        ?string $rawPhone,
        string $message,
        array $meta = [],
    ): ?SmsMessage {
        $to = PhoneNumber::normalize($rawPhone, $this->config['country_code'] ?? '251');

        if (! $to) {
            Log::warning('[SMS] skipped — invalid/missing phone', [
                'raw_phone' => $rawPhone,
                'template_key' => $meta['template_key'] ?? null,
            ]);
            return null;
        }

        $record = SmsMessage::create([
            'to'              => $to,
            'raw_phone'       => $rawPhone,
            'body'            => $message,
            'template_key'    => $meta['template_key'] ?? null,
            'notifiable_type' => $meta['notifiable_type'] ?? null,
            'notifiable_id'   => $meta['notifiable_id'] ?? null,
            'violator_id'     => $meta['violator_id'] ?? null,
            'driver'          => $this->defaultDriver(),
            'status'          => 'queued',
            'meta'            => $meta['meta'] ?? null,
        ]);

        if ($this->config['dry_run'] ?? false) {
            $record->markSent('dry-run-' . $record->id);
            return $record;
        }

        $gateway = $this->gateway();
        $result = $gateway->send($to, $message, ['from' => $this->config['from'] ?? null]);

        if ($result->success) {
            $record->markSent($result->providerMessageId);
        } else {
            $record->markFailed($result->error ?? 'unknown_error');
        }

        return $record;
    }

    public function gateway(?string $driver = null): SmsGateway
    {
        $driver = $driver ?: $this->defaultDriver();

        return $this->resolved[$driver] ??= $this->make($driver);
    }

    public function defaultDriver(): string
    {
        return $this->config['default'] ?? 'log';
    }

    protected function make(string $driver): SmsGateway
    {
        $cfg = $this->config['drivers'][$driver] ?? null;

        return match ($driver) {
            'log'         => new Drivers\LogSmsGateway($cfg['channel'] ?? 'stack'),
            'afromessage' => new Drivers\AfromessageSmsGateway($cfg ?? []),
            default       => throw new InvalidArgumentException("SMS driver [{$driver}] is not supported."),
        };
    }
}
