<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * - Applies the user's preferred locale from the session on every request.
     *   Works in tandem with the /language/{locale} route in web.php.
     */
    public function boot(): void
    {
        // ── Locale resolution from session ──────────────────────────────
        // Read the locale stored by the /language/{locale} route and apply
        // it so all __() calls and Filament labels honour the user's choice.
        $this->app->singleton('locale.boot', function () {
            if (app()->runningInConsole()) {
                return null;
            }
            $locale = session('locale', config('app.locale', 'en'));
            if (in_array($locale, ['en', 'am'])) {
                app()->setLocale($locale);
            }
            return $locale;
        });

        // Resolve on every web request
        app('locale.boot');
    }
}

