<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\Contracts\SmsGateway;
use App\Services\Sms\SmsResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class AfromessageSmsGateway implements SmsGateway
{
    public function __construct(private readonly array $config)
    {
    }

    public function send(string $to, string $message, array $options = []): SmsResult
    {
        if (empty($this->config['token'])) {
            return SmsResult::fail('Afromessage token is not configured');
        }

        $payload = array_filter([
            'to'       => $to,
            'message'  => $message,
            'from'     => $options['from'] ?? $this->config['identifier_id'] ?? null,
            'sender'   => $options['sender'] ?? $this->config['sender'] ?? null,
            'callback' => $this->config['callback'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $response = Http::withToken($this->config['token'])
                ->timeout($this->config['timeout'] ?? 15)
                ->acceptJson()
                ->asJson()
                ->post($this->config['base_url'], $payload);
        } catch (ConnectionException $e) {
            return SmsResult::fail('connection_error: ' . $e->getMessage());
        } catch (RequestException $e) {
            return SmsResult::fail('request_error: ' . $e->getMessage(), (array) $e->response?->json());
        }

        $data = $response->json() ?? [];

        if (! $response->successful() || (int) ($data['acknowledge'] ?? 0) !== 1 && ($data['status'] ?? null) !== 'success') {
            $error = $data['response']['errors'] ?? $data['message'] ?? $response->reason() ?? 'unknown_error';
            return SmsResult::fail(is_array($error) ? json_encode($error) : (string) $error, $data);
        }

        $messageId = $data['response']['message_id']
            ?? $data['message_id']
            ?? $data['response']['status']
            ?? null;

        return SmsResult::ok($messageId ? (string) $messageId : null, $data);
    }

    public function name(): string
    {
        return 'afromessage';
    }
}
