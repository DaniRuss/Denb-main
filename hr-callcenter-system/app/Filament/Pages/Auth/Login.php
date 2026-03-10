<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $login = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $remember = $data['remember'] ?? false;

        $guard = Filament::auth();

        $attempted = $guard->attempt(
            ['email' => $login, 'password' => $password],
            $remember
        );

        if (! $attempted) {
            $attempted = $guard->attempt(
                ['username' => $login, 'password' => $password],
                $remember
            );
        }

        if (! $attempted) {
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login = $data['email'] ?? '';

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => $login,
                'password' => $data['password'] ?? '',
            ];
        }

        return [
            'username' => $login,
            'password' => $data['password'] ?? '',
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email or Username')
            ->required()
            ->autocomplete()
            ->autofocus();
    }
}
