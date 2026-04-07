<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales', ['en', 'am']);
        $defaultLocale = config('app.locale');
        $locale = $request->session()->get('locale', $defaultLocale);

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        if (! $request->session()->has('locale')) {
            $request->session()->put('locale', $locale ?: $defaultLocale);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
