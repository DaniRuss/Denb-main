<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
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
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn(): string => new HtmlString('
                <style>
                    /* Expand the outermost containers */
                    .fi-main-ctn, .fi-page, .fi-main, .fi-sc-form { 
                        max-width: none !important; 
                        width: 100% !important; 
                    }
                    /* Force any grid inside the form to be 1-column or elements to span full */
                    .fi-sc-form .fi-grid {
                        grid-template-columns: 1fr !important;
                    }
                    .fi-sc-form .fi-grid > * {
                        grid-column: span 1 / span 1 !important;
                    }
                    /* Ensure tabs and other large components use all space */
                    .fi-tabs {
                        width: 100% !important;
                    }
                </style>
            '),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_START,
            fn(): string => view('filament.partials.language-switcher')->render(),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
            fn(): string => new HtmlString(
                '<div class="mb-4 flex justify-center">' . view('filament.partials.language-switcher')->render() . '</div>'
            ),
        );
    }
}
