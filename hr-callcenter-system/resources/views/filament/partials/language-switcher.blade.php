@php
    $currentLocale = app()->getLocale();
    $supportedLocales = config('app.supported_locales', ['en', 'am']);
@endphp

<div class="flex items-center gap-2">
    @foreach($supportedLocales as $locale)
        <a
            href="{{ route('locale.switch', ['locale' => $locale]) }}"
            class="fi-btn fi-size-sm inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium transition {{ $currentLocale === $locale ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-100 dark:ring-gray-700 dark:hover:bg-gray-800' }}"
        >
            {{ strtoupper($locale) }}
        </a>
    @endforeach
</div>
