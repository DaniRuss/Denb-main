<?php

namespace App\Providers;

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
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::HEAD_END,
            fn(): string => new \Illuminate\Support\HtmlString('
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
    }
}
